<?php
namespace Firebear\CloudFlare\Controller\Adminhtml\ApiControl;

use \Magento\Backend\App\Action as BackendAction;
use Magento\Backend\App\Action\Context;
use \Magento\Framework\Json\Helper\Data as JsonHelper;
use \Firebear\CloudFlare\Helper\Config;

class ZoneSettings extends BackendAction
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
     * @var configHelper
     */
    protected $configHelper;

    /**
     * ZoneSettings constructor.
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
        $name = $this->getRequest()->getParam('name');
        $value = $this->getRequest()->getParam('value');

        if ($this->getRequest()->getParam('type') == 'json') {
            $value = $this->jsonHelper->jsonDecode($value);
        }

        $zone = $this->configHelper->getZone();
        $zoneSettingsClient = $this->configHelper->getSettingConnection();
        
        $method = 'change_' . $name;
        if (method_exists($zoneSettingsClient, $method) && $zone) {
            $result = $zoneSettingsClient->$method($zone['id'], $value);
        }

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
