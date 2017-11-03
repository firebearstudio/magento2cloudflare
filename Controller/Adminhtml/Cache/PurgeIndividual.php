<?php
/**
 * Copyright © 2016 Firebear Studio. All rights reserved.
 */

namespace Firebear\CloudFlare\Controller\Adminhtml\Cache;

use \Magento\Backend\App\Action as BackendAction;
use \Magento\Backend\App\Action\Context;
use \Magento\Backend\App\Action;
use \Magento\Framework\Json\Helper\Data as JsonHelper;
use \Firebear\CloudFlare\Helper\Config;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\Controller\ResultFactory;

class PurgeIndividual extends BackendAction
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Firebear_CloudFlare::cache_purge_individual';

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
     * Result page factory.
     *
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * Ip constructor.
     *
     * @param Context     $context
     * @param JsonHelper  $jsonHelper
     * @param Config      $configHelper
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        JsonHelper $jsonHelper,
        Config $configHelper,
        PageFactory $pageFactory
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->configHelper = $configHelper;
        $this->pageFactory = $pageFactory;
        parent::__construct($context);
    }

    /**
     * Check if cache management is allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Backend::cache');
    }

    /**
     * Execute action.
     *
     * @return PageFactory
     */
    public function execute()
    {
        $files = $this->_request->getParam('files');
        $domain = $this->_request->getParam('domain');

        try {

            $filesArray = preg_split('/\r\n|[\r\n]|,/', $files);
            $result = $this->configHelper->purgeIndividual($domain, $filesArray);

            if ($result) {  
                $this->_eventManager->dispatch('clean_cloudflare_cache_individual_after');

                $responseArray['success'] = true;
                $responseArray['message'] = __(
                    'Successfully purged individual assets. Please allow up to 30 seconds for changes to take effect.'
                );
            } else {
                $responseArray['error'] = true;
                $responseArray['message'] = __('There was a problem clearing the cache.');
            }

        } catch (\Exception $e) {
            $responseArray['error'] = true;
            $responseArray['message'] = $e->getMessage();
        }

        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($responseArray)
        );
    }
}
