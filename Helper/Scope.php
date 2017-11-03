<?php
namespace Firebear\CloudFlare\Helper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
class Scope extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Configuration section id
     */
    CONST SYSTEM_SECTION = "firebear_cloudflare_control";

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    
    
    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return array
     */
    public function getCurrentScope()
    {
        if ($this->_request->getParam(ScopeInterface::SCOPE_STORE)) {

            $scopeType = ScopeInterface::SCOPE_STORE;
            $scopeCode = $this->storeManager
                ->getStore($this->_request->getParam(ScopeInterface::SCOPE_STORE))
                ->getCode();

        } elseif ($this->_request->getParam(ScopeInterface::SCOPE_WEBSITE)) {

            $scopeType = ScopeInterface::SCOPE_WEBSITE;
            $scopeCode = $this->storeManager
                ->getWebsite($this->_request->getParam(ScopeInterface::SCOPE_WEBSITE))
                ->getCode();
        } else {
            $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
            $scopeCode = 0;
        }
        return [$scopeType, $scopeCode];
    }

    /**
     * @return mixed
     */
    public function getCurrentStore()
    {
        return $this->_request->getParam(ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getCurrentWebsite()
    {
        return $this->_request->getParam(ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return setting
     */
    public function getSection()
    {
        return self::SYSTEM_SECTION;
    }

    /**
     * @param        $threeScopes
     * @param string $groupName
     *
     * @return array
     */
    public function getThreeScope($threeScopes, $groupName = 'apioptions')
    {
        $arrayField = [];
        $arrayResult = [];
        foreach ($threeScopes as $key => $value) {
            $arrayField["fields"][$key]['value'] = $value;
        }
        $arrayResult[$groupName] = $arrayField;
        
        return $arrayResult;
    }

    /**
     * @param $threeScopes
     *
     * @return array
     */
    public function getMapScope($threeScopes)
    {
        $arrayField = [];
        $arrayResult = [];
        foreach ($threeScopes as $key=>$value) {
            $arrayField["fields"][$key]['value'] = $value;
        }
        $arrayResult['overview']=$arrayField;
        return $arrayResult;
    }
}
