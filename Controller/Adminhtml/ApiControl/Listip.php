<?php
namespace Firebear\CloudFlare\Controller\Adminhtml\ApiControl;
use Magento\Backend\App\Action\Context;
class Listip extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;
    /**
     * Config configHelper
     *
     * @var configHelper
     */
    protected $configHelper;
    public function __construct(
        Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Firebear\CloudFlare\Helper\Config $configHelper
        
    ) {
        $this->jsonHelper = $jsonHelper;
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

    public function execute()
    {
      $isupdated = $this->configHelper->getListIps();
     /*
     echo "<pre>";
      var_dump($isupdated);die();
     echo "</pre>";
     */
        if( $isupdated[0] == 2)
        {
        $response_array['status'] = 'success';
        $response_array['result'] = $isupdated[1];
        }
        elseif( $isupdated == 3 )
        {
        $response_array['status'] = 'error';
        $response_array['message'] = 'The operation is not done something is wrong!';
        }
        elseif( $isupdated == 1 )
        {
        $response_array['status'] = 'error';
        $response_array['message'] = 'The domain name is wrong! Please change and try again';
        }
        else
        {
        $response_array['status'] = 'error';
        $response_array['message'] = 'The IPS List not loaded something is wrong! Please verify the Email and Key';
        }

      return $this->getResponse()->representJson($this->jsonHelper->jsonEncode($response_array));
    }
}
