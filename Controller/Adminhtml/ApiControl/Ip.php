<?php
namespace Firebear\CloudFlare\Controller\Adminhtml\ApiControl;

use \Magento\Backend\App\Action as BackendAction;
use Magento\Backend\App\Action\Context;
use \Magento\Backend\App\Action;
use \Magento\Framework\Json\Helper\Data as JsonHelper;
use \Firebear\CloudFlare\Helper\Config;

class Ip extends BackendAction
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Firebear_CloudFlare::firebear_cloudflare_control';
    
    /**
     * JSON helper.
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;
    /**
     * Config configHelper
     *
     * @var Config
     */
    protected $configHelper;

    /**
     * Ip constructor.
     *
     * @param Context    $context
     * @param JsonHelper $jsonHelper
     * @param Config     $configHelper
     */
    public function __construct(
        Context $context,
        JsonHelper $jsonHelper,
        Config $configHelper
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->configHelper = $configHelper;
        parent::__construct($context);
    }

    /**
     * Execute action.
     *
     * @return string JSON.
     */
    public function execute()
    {
        $operation = $this->getRequest()->getParam('op');
        $requestedIP = $this->getRequest()->getParam('api_ip_value');
        $firewall = $this->configHelper->getFirewallConnection();
        $zone = $this->configHelper->getZone();
        $result = $firewall->create($zone['id'], $operation, ["target" => "ip", "value" => $requestedIP]);

        $response = [];
        if (isset($result['success']) && $result['success']) {
            $response['status'] = 'success';
        } elseif (isset($result['result']['errors'])) {
            $response['status'] = 'error';
            $response['message'] = $result['result']['errors'][0]['message'];
        } else {
            $response['status'] = 'error';
            $response['message'] = __(
                'The configuration is not updated something is wrong! Please verify the Email and Key'
            );
        }

        return $this->getResponse()->representJson($this->jsonHelper->jsonEncode($response));
    }
}
