<?php
namespace Firebear\CloudFlare\Controller\Adminhtml\ApiControl;

use Magento\Backend\App\Action\Context;
use \Firebear\CloudFlare\Helper\Config;
use Cloudflare;
use Cloudflare\User;

class Check extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * Config configResource
     *
     * @var configResource
     */
    protected $configResource;
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

    /**
     * Config configHelper
     *
     * @var Config
     */
    protected $configHelper;

    /**
     * Check constructor.
     *
     * @param Context                                    $context
     * @param \Magento\Framework\Json\Helper\Data        $jsonHelper
     * @param \Magento\Config\Model\ResourceModel\Config $configResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Config\ScopeInterface   $configScope
     * @param \Firebear\CloudFlare\Helper\Scope          $internScope
     * @param \Magento\Config\Model\Config\Factory       $configFactory
     * @param Config                                     $configHelper
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Config\Model\ResourceModel\Config $configResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Config\ScopeInterface $configScope,
        \Firebear\CloudFlare\Helper\Scope $internScope,
        \Magento\Config\Model\Config\Factory $configFactory,
        Config $configHelper
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->configResource  = $configResource;
        $this->storeManager = $storeManager;
        $this->internScope = $internScope;
        $this->_configFactory = $configFactory;
        $this->configHelper = $configHelper;
        parent::__construct($context);
    }
    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Firebear_CloudFlare::firebear_cloudflare_control');
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $apiEmail = $this->getRequest()->getParam('api_email_value');
        $apiKey = $this->getRequest()->getParam('api_key_value');
        $apiDomain = $this->getRequest()->getParam('api_domain_value');
        $client = new User($apiEmail, $apiKey);
        $user = $client->user();

        if (!isset($user['result']['errors']) && isset($user['success']) && $user['success']) {
            $responseArray['status'] = 'success';
            $responseArray['cl'] = $user;
            
            $threeScope = ["api_email" => $apiEmail, "api_key" => $apiKey, "api_domain" => $apiDomain];

            $groupForSave = $this->internScope->getThreeScope($threeScope);
            
            $configData = [
                'section' => $this->internScope->getSection(),
                'website' => $this->internScope->getCurrentWebsite(),
                'store' => $this->internScope->getCurrentStore(),
                'groups' => $groupForSave,
            ];

            $configModel = $this->_configFactory->create(['data' => $configData]);
            $configModel->save();

            $syncComplete = $this->configHelper->syncSettings();

            if ($syncComplete) {
                $this->messageManager->addSuccess(__('CloudFlare settings was successfully saved.'));
                $responseArray['reload'] = true;
            } else {
                $responseArray['status'] = 'error';
                $responseArray['message'] = __(
                    "Can't retrieve settings from CloudFlare. Please check your API Access setails"
                );
            }

        } else {
            $responseArray['status'] = 'error';
        }
        return $this->getResponse()->representJson($this->jsonHelper->jsonEncode($responseArray));
    }
}
