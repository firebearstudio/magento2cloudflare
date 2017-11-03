<?php
namespace Firebear\CloudFlare\Helper;

use Cloudflare\Zone;
use Cloudflare\Zone\Settings;
use Cloudflare\Zone\Cache;
use Cloudflare\Zone\Analytics;
use Cloudflare\User;
use Cloudflare\Zone\Firewall\AccessRules;
use Symfony\Component\Config\Definition\Exception\Exception;
use \Magento\Store\Model\ScopeInterface;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
    * @var setting field array
    */
    public static $settingFieldMapper = array("security_level" => "security" , "cache_level" => "cachelvl","development_mode" => "devmode","ipv6" => "ipv46","minify" => "minify","rocket_loader" => "async");
    /**
    * @var setting field array
    */
    public static $resultMapper = array("requests" => array('all','cached','uncached','ssl','http_status','content_type','country','ip_class') , "bandwidth" => array('all','cached','uncached','ssl','content_type','country'),"threats" => array('all','type','country'),"pageviews" => array('all','search_engine'),"uniques" => array('all'));
    /**
    * @var setting field array
    */
    public static $simpleIp = array("mode","configuration","created_on");
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /**
     * ScopeConfigInterface scopeConfig
     *
     * @var scopeConfig
     */
    protected $scopeConfig;
    /**
     * Config configResource
     *
     * @var configResource
     */
    protected $configResource;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var string
     */
    protected $apiEmail;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $apiDomain;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
   
    /**
     * @var \Firebear\CloudFlare\Helper\Scope
     */
    protected $internScope;
    /**
     * Backend Config Model Factory
     *
     * @var \Magento\Config\Model\Config\Factory
     */
    protected $_configFactory;
    
    protected $scopeType;
    protected $scopeCode ;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Config\Model\ResourceModel\Config $configResource,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Config\ScopeInterface $configScope,
        \Firebear\CloudFlare\Helper\Scope $internScope,
        \Magento\Config\Model\Config\Factory $configFactory
    ) {
        $this->messageManager = $messageManager;
        $this->configResource  = $configResource;
        $this->jsonHelper = $jsonHelper;
        $this->storeManager = $storeManager;
        $this->internScope = $internScope;
        $this->_configFactory = $configFactory;
        $this->initIntern();

        parent::__construct($context);
    }

    /**
     * Return array of since labels & time formats
     * 
     * @return array
     */
    public function getAnalyticsSinceAsArray()
    {
        return [
            '-30' => [
                'label' => 'Last 30 minutes',
                'format' => '%M'
            ],
            '-360' => [
                'label' => 'Last 6 hours',
                'format' => '%H:%M%p'
            ],
            '-720' => [
                'label' =>'Last 12 hours',
                'format' => '%H:%M%p'
            ],
            '-1440' => [
                'label' =>'Last 24 hours',
                'format' => '%a, %H%p'
            ],
            '-10080' => [
                'label' =>'Last week',
                'format' => '%a, %H%p'
            ],
            '-43200' => [
                'label' =>'Last month',
                'format' => '%e %b, %H:%M%p'
            ]
        ];
    }
    
    /**
     * Retrieve since params by since value.
     * 
     * @param $since
     *
     * @return mixed|null
     */
    public function getAnalyticsSinceParams($since)
    {
        $sinceArray = $this->getAnalyticsSinceAsArray();
        if (isset($sinceArray[$since])) {
            return $sinceArray[$since];
        }
        
        return null;
    }

    /**
     * Load CloudFlare zone settings and save them to magento system config.
     *
     * @return bool
     * @throws \Exception
     */
    public function syncSettings()
    {
        $zone = $this->getZone();
        if (isset($zone['id'])) {
            $settingsConnection = $this->getSettingConnection();
            $zoneSettings = $settingsConnection->settings($zone['id']);

            if (!isset($zoneSettings['result']['errors'])) {
                $result = [];
                foreach ($zoneSettings['result'] as $setting) {
                    if (is_array($setting['value'])) {
                        $result[$setting['id']] = $this->jsonHelper->jsonEncode($setting['value']);
                    } else {
                        $result[$setting['id']] = $setting['value'];
                    }
                }

                $groupForSave = $this->internScope->getThreeScope($result, 'zone_settings');
                $configData = [
                    'section' => $this->internScope->getSection(),
                    'website' => $this->internScope->getCurrentWebsite(),
                    'store' => $this->internScope->getCurrentStore(),
                    'groups' => $groupForSave,
                ];

                $configModel = $this->_configFactory->create(['data' => $configData]);
                $configModel->save();

                return true;
            }
        }

        return false;
    }

    /**
     * Prepare saved settings zones.
     *
     * @return array
     */
    public function getZones()
    {
        $zones = [];
        $allStores = $this->storeManager->getStores();
        foreach ($allStores as $store) {
            $domain = $this->scopeConfig->getValue(
                'firebear_cloudflare_control/apioptions/api_domain',
                ScopeInterface::SCOPE_STORE,
                $store
            );
            $zone = [
                'domain' => $domain,
                'store' => $store->getCode()
            ];
            $zones[$domain] = $zone;
        }

        return $zones;
    }

    /**
     * Purge CloudFlare cache.
     *
     * @param $zone
     *
     * @return bool
     */
    public function purgeEverything($zone)
    {
        $zones = $this->getZones();

        if (isset($zones[$zone])) {
            $storeCode = $zones[$zone]['store'];

            $this->apiEmail = $this->scopeConfig->getValue(
                'firebear_cloudflare_control/apioptions/api_email',
                ScopeInterface::SCOPE_STORE,
                $storeCode
            );
            $this->apiKey = $this->scopeConfig->getValue(
                'firebear_cloudflare_control/apioptions/api_key',
                ScopeInterface::SCOPE_STORE,
                $storeCode
            );
            $this->apiDomain = $this->scopeConfig->getValue(
                'firebear_cloudflare_control/apioptions/api_domain',
                ScopeInterface::SCOPE_STORE,
                $storeCode
            );

            $cacheConnection = $this->getCacheConnection();
            $zone = $this->getZone();
            $result = $cacheConnection->purge($zone['id'], true);
            if (isset($result['success']) && $result['success']) {
                return true;
            } elseif (isset($result['result']['errors'][0]['message'])) {
                throw new Exception(
                    __($result['result']['errors'][0]['message'])
                );
            }
        }

        return false;
    }

    /**
     * Purge CloudFlare Individual Files.
     *
     * @param $zone
     * @param $files
     *
     * @return bool
     */
    public function purgeIndividual($zone, $files)
    {
        $zones = $this->getZones();

        if (isset($zones[$zone])) {
            $storeCode = $zones[$zone]['store'];

            $this->apiEmail = $this->scopeConfig->getValue(
                'firebear_cloudflare_control/apioptions/api_email',
                ScopeInterface::SCOPE_STORE,
                $storeCode
            );
            $this->apiKey = $this->scopeConfig->getValue(
                'firebear_cloudflare_control/apioptions/api_key',
                ScopeInterface::SCOPE_STORE,
                $storeCode
            );
            $this->apiDomain = $this->scopeConfig->getValue(
                'firebear_cloudflare_control/apioptions/api_domain',
                ScopeInterface::SCOPE_STORE,
                $storeCode
            );
            
            $cacheConnection = $this->getCacheConnection();
            $zone = $this->getZone();
            $result = $cacheConnection->purge_files($zone['id'], $files);

            if (isset($result['success']) && $result['success']) {
                return true;
            } elseif (isset($result['result']['errors'][0]['message'])) {
                throw new Exception(
                    __($result['result']['errors'][0]['message'])
                );
            }
        } else {
            throw new Exception(
                __('Undefined zone: ' . $zone)
            );
        }

        return false;
    }

    /**
     * Initialize current scope.
     *
     * @return $this
     */
    public function initIntern()
    {
        $valueScope = $this->internScope->getCurrentScope();
        $this->scopeType = $valueScope[0];
        $this->scopeCode = $valueScope[1];
        $this->apiEmail = $this->scopeConfig->getValue(
            'firebear_cloudflare_control/apioptions/api_email',
            $this->scopeType,
            $this->scopeCode
        );
        $this->apiKey = $this->scopeConfig->getValue(
            'firebear_cloudflare_control/apioptions/api_key',
            $this->scopeType,
            $this->scopeCode
        );
        $this->apiDomain = $this->scopeConfig->getValue(
            'firebear_cloudflare_control/apioptions/api_domain',
            $this->scopeType,
            $this->scopeCode
        );

        return $this;
    }

    /**
     * Retrieve Zone Settings connection.
     *
     * @return Settings
     */
    public function getSettingConnection()
    {
        return new Settings($this->apiEmail, $this->apiKey);
    }

    /**
     * Retrieve User connection.
     *
     * @return array
     */
    public function getUserConnection()
    {
        $client = new User($this->apiEmail, $this->apiKey);
        return $client->user();
    }

    /**
     * Retrieve Cache connection.
     *
     * @return Cache
     */
    public function getCacheConnection()
    {
        return new Cache($this->apiEmail, $this->apiKey);
    }

    /**
     * Retrieve Zone Access Rules connection.
     *
     * @return AccessRules
     */
    public function getFirewallConnection()
    {
        return new AccessRules($this->apiEmail, $this->apiKey);
    }

    /**
     * Retrieve Zone Analytics connection.
     *
     * @return Analytics
     */
    public function getAnalyticsConnection()
    {
        return new Analytics($this->apiEmail, $this->apiKey);
    }

    /**
     * Retrieve Zone Dns connection.
     *
     * @return Analytics
     */
    public function getDnsConnection()
    {
        return new Zone\Dns($this->apiEmail, $this->apiKey);
    }

    /**
     * Retrieve current Zone.
     *
     * @return array
     */
    public function getZone()
    {
        $zone = new Zone($this->apiEmail, $this->apiKey);
        $zones = $zone->zones($this->apiDomain);
        if (isset($zones['result'][0])) {
            return $zones['result'][0];
        }

        return [];
    }

    /**
     * Get current domain name.
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->apiDomain;
    }

    /**
     * Call API Client.
     *
     * @param $functionCall
     *
     * @return int
     */
    public function apiClient($functionCall)
    {
        $user = $this->getUserConnection();
        $userArray = (array) $user;

        if (!isset($userArray['error'])) {
            $clientSetting = $this->getSettingConnection();
            $zone = new Zone($this->apiEmail, $this->apiKey);
            $zones = $zone->zones($this->apiDomain);
            //$zoneArray = (array) $zone;
            if (isset($zones['result'][0])) {

                if ($functionCall == "save") {
                    $this->saveApi($clientSetting, $zones);
                    return 2;

                } elseif ($functionCall == "load") {
                    $this->loadApi($clientSetting, $zones);
                    return 2;

                } elseif (
                    $functionCall == "purge" ||
                    $functionCall == "ip" ||
                    $functionCall == "dashboard" ||
                    $functionCall =="listips"
                ) {
                    return $zones['result'][0]['id'];
                } else {
                    return 0;
                }
            } else {
                $this->messageManager->addError(
                    __("The domain name is wrong! Please change and try again")
                );
            }

            return 1;

        } else {
            //user not exist
            $this->messageManager->addError(
                __("The configuration is not updated something is wrong! Please verify the Email and Key")
            );
            return 0;
        }
        
        //$zones = $client->zoneByName($api_domain);
        
    }
    /**
      * Save API Client
    */
    public function saveApi($clientSetting,$zone)
    {
             $mapper = self::$settingFieldMapper;
             $settingValue = array();
             foreach($mapper as $keyMapper => $valueMapper)
             {
                $configValue = $this->scopeConfig->getValue('firebear_cloudflare_control/overview/'.$valueMapper, $this->scopeType , $this->scopeCode);
                 if(!empty($configValue))
                 {
                  if($keyMapper=="minify")
                  {
                    $valueArray = explode("-", $configValue);
                    $configValue = array('value' =>array('js' => $valueArray['0'],'css' => $valueArray['1'],'html' => $valueArray['2']));
                  }
                   $clientSetting->{"change_".$keyMapper}($zone->result[0]->id,$configValue);
                   //$settingValue[] = array('id' => $keyMapper , 'value' => $configValue);
                 }                
              } 
            // $clientSetting->edit($zone->result[0]->id,$settingValue);
             $this->messageManager->addSuccess("The settings is updated to Cloudflare");
             
    }
    /**
      * Load API Client
    */
    public function loadApi($clientSetting, $zone)
    {
         $results = $clientSetting->settings($zone['result'][0]['id']);
         $arrayToMapScopt = array();
         foreach($results['result'] as $result)
         {
           $mapper = self::$settingFieldMapper;
           $result = (array) $result;
           if(is_array($result))
           {

           $id = $result['id'];
           $value = $result['value'];

           if(array_key_exists($id , $mapper))
             {
              if(!is_string($value) && !is_int($value))
              {
                 $valueMinify = (array) $value;
                 if(isset($valueMinify['js']))
                 {
                   $value = $valueMinify['js']."-".$valueMinify['css']."-".$valueMinify['html'];
                 }
              }
              if(!empty($value))
              {
              //$this->configResource->saveConfig('firebear_cloudflare_control/overview/'.$mapper[$id], $value,$this->scopeType,$this->scopeCode);
              $arrayToMapScopt[$mapper[$id]] = $value;

              }
             }
           }
         }

        $groupForSave = $this->internScope->getMapScope($arrayToMapScopt);

         $configData = [
               'section' => $this->internScope->getSection(),
               'website' => $this->internScope->getCurrentWebsite(),
               'store' => $this->internScope->getCurrentStore(),
               'groups' => $groupForSave,
         ];
         $configModel = $this->_configFactory->create(['data' => $configData]);
         $configModel->save();    
         $this->messageManager->addSuccess("The settings is loaded from Cloudflare");        
    }
    /**
      * Load and save config
    */
    public function purgeUrl($without = 0,$cache_url = null)
    {
      $zone_id = $this->apiClient('purge');    
      $cache = $this->getCacheConnection();
      if($without==1)
      {
        if(!$cache_url == null)
        {      
          $files = array($cache_url);
          $purge_cache=$cache->purge_files($zone_id,$files);
          $purge_cache = (array) $purge_cache;
          if(isset($purge_cache['error']))
          {
            return 3;
          }
          else
          {
            return 2;
          }
          
        }
        else
        {
          return 0;
        }
      }
      else
      {
          $purge_cache=$cache->purge($zone_id);
          $purge_cache = (array) $purge_cache;
          if(isset($purge_cache['error']))
          {
            return 3;
          }
          else
          {
            return 2;
          }
          return 2;
      }

    }
    /**
      * Load and save config
    */
    public function operationIP($op = 'w',$ip)
    {
        $zone_id = $this->apiClient('ip');    
        $firewall = $this->getFirewallConnection();

        if(!$ip == null)
        { 
            $operation = null; 
            switch ($op) {
                case 'w':
                    $operation = 'whitelist';
                    break;
                case 'b':
                    $operation = 'block';
                    break;
                case 'u':
                    $operation = 'challenge';
                    break;
            }
            return $firewall->create($zone_id, $operation, array("target" => "ip", "value" => $ip));
        } else {
            return 0;
        }
    }
    public function levelConverter($toMaps = array())
    {
     $bigResultMapper = array();
     $contantsMappers = self::$resultMapper;
     foreach($toMaps as  $valueToMap)
     {
        
       foreach($contantsMappers as $keyContantsMapper => $valuesContantsMapper)
       {

          if(isset($valueToMap[$keyContantsMapper]))
          {
            foreach($valuesContantsMapper as $valuesContantsMapper)
            {
              if(isset($valueToMap[$keyContantsMapper][$valuesContantsMapper]) && is_array($valueToMap[$keyContantsMapper][$valuesContantsMapper]))
              {
                foreach($valueToMap[$keyContantsMapper][$valuesContantsMapper] as $keyLessLevel =>$valueLessLevel)
                {
                  $bigResultMapper[$keyContantsMapper][$valuesContantsMapper][$keyLessLevel] = isset($bigResultMapper[$keyContantsMapper][$valuesContantsMapper][$keyLessLevel]) ? $bigResultMapper[$keyContantsMapper][$valuesContantsMapper][$keyLessLevel] + $valueLessLevel : $valueLessLevel;
                }
              }
              else
              {
                  

                  if($keyContantsMapper != "uniques" )
                   {

                      $bigResultMapper[$keyContantsMapper][$valuesContantsMapper] = isset($bigResultMapper[$keyContantsMapper][$valuesContantsMapper]) ? ($bigResultMapper[$keyContantsMapper][$valuesContantsMapper] + $valueToMap[$keyContantsMapper][$valuesContantsMapper]) : $valueToMap[$keyContantsMapper][$valuesContantsMapper];
                  
                   }
                   else
                   {
                     if(!isset($bigResultMapper[$keyContantsMapper][$valuesContantsMapper]))
                     {
                        $bigResultMapper[$keyContantsMapper][$valuesContantsMapper] = $valueToMap[$keyContantsMapper][$valuesContantsMapper];
                     }
                   }

              

              }
            }
          }
       }
      }
      return $bigResultMapper;
    }
    public function levelDayConverter($toMaps = array())
    {
     $bigResultMapper = array();
     $contantsMappers = self::$resultMapper;
     foreach($toMaps as  $valueToMap)
     {
        
       foreach($contantsMappers as $keyContantsMapper => $valuesContantsMapper)
       {

          if(isset($valueToMap[$keyContantsMapper]))
          {
            foreach($valuesContantsMapper as $valuesContantsMapper)
            {
              if(isset($valueToMap[$keyContantsMapper][$valuesContantsMapper]) && is_array($valueToMap[$keyContantsMapper][$valuesContantsMapper]))
              {
                foreach($valueToMap[$keyContantsMapper][$valuesContantsMapper] as $keyLessLevel =>$valueLessLevel)
                {
                  $bigResultMapper[$keyContantsMapper][$valuesContantsMapper][$keyLessLevel] = $valueLessLevel;
                }
              }
              else
              {
                  $bigResultMapper[$keyContantsMapper][$valuesContantsMapper] =  $valueToMap[$keyContantsMapper][$valuesContantsMapper];
                  
              }
            }
          }
       }
      }
      return $bigResultMapper;
    }

    /**
     * @param $since
     *
     * @return array
     */
    public function getDashboard($zone, $since)
    {
        $zones = $this->getZones();

        if (isset($zones[$zone])) {
            $storeCode = $zones[$zone]['store'];

            $this->apiEmail = $this->scopeConfig->getValue(
                'firebear_cloudflare_control/apioptions/api_email',
                ScopeInterface::SCOPE_STORE,
                $storeCode
            );
            $this->apiKey = $this->scopeConfig->getValue(
                'firebear_cloudflare_control/apioptions/api_key',
                ScopeInterface::SCOPE_STORE,
                $storeCode
            );
            $this->apiDomain = $this->scopeConfig->getValue(
                'firebear_cloudflare_control/apioptions/api_domain',
                ScopeInterface::SCOPE_STORE,
                $storeCode
            );

            $zoneId = $this->apiClient('dashboard');
            $analytics = $this->getAnalyticsConnection();

            return $analytics->dashboard($zoneId, $since, 0, false);
        }
    }

    public function simpleListIps($convertFirewalls)
    {
     $resultConvertFirewall = array();
     foreach($convertFirewalls as $convertFirewall)
     {
       $resultConvertFirewall[] = array('mode' => $convertFirewall['mode'],'value' => $convertFirewall['configuration']['value'],'created_on' => $convertFirewall['created_on']);
     }
     return $resultConvertFirewall;
    }
    public function getListIps()
    {
       $zone_id = $this->apiClient('listips'); 
       $firewall = $this->getFirewallConnection();
       $data = array("per_page" => 200," scope_type" => "zone","direction" => "desc","order" => "mode");
       $firewall = $firewall->rules($zone_id, $data);
       $firewallResult = (array) $firewall;
       
       if(isset($firewallResult['error']))
        {
         return array(3,null);
        }
        else
        {         
           $convertFirewall = $this->jsonHelper->jsonDecode($this->jsonHelper->jsonEncode($firewallResult));        
         $convertFirewall = $this->simpleListIps($convertFirewall['result']);
         return array(2,$convertFirewall);
        }
       return array(1,null) ;
    }
    public function loadConfig()
    {
       return $this->apiClient('load');
    }
    public function saveConfig()
    {
       $this->apiClient('save');
    }
}
