<?php
/**
 * Copyright © 2016 Firebear Studio. All rights reserved.
 */

namespace Firebear\CloudFlare\Controller\Adminhtml\Analytics;

use \Magento\Backend\App\Action as BackendAction;
use Magento\Backend\App\Action\Context;
use \Magento\Backend\App\Action;
use \Magento\Framework\Json\Helper\Data as JsonHelper;
use \Firebear\CloudFlare\Helper\Config;
use \Magento\Framework\View\Result\PageFactory;

class Index extends BackendAction
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Firebear_CloudFlare::zone_analytics';

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
     * Execute action.
     *
     * @return PageFactory
     */
    public function execute()
    {
        $resultPage = $this->pageFactory->create();
        return $resultPage;
    }
}
