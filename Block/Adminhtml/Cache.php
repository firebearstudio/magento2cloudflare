<?php
/**
 * Copyright © 2016 Firebear Studio. All rights reserved.
 */
namespace Firebear\CloudFlare\Block\Adminhtml;

use \Firebear\CloudFlare\Helper\Config;
use \Magento\Backend\Block\Cache\Additional;
use \Magento\Backend\Block\Template\Context;

/**
 * Class Cache
 * @package Firebear\CloudFlare\Block\Adminhtml
 */
class Cache extends Additional
{

    /**
     * CloudFlare Config helper.
     * 
     * @var Config
     */
    protected $configHelper;

    /**
     * Cache constructor.
     *
     * @param Config    $configHelper
     * @param Context   $context
     * @param array     $data
     */
    public function __construct(
        Config $configHelper,
        Context $context,
        array $data = []
    ) {
        $this->configHelper = $configHelper;
        parent::__construct($context, $data);
    }

    /**
     * Prepare saved settings zones.
     *
     * @return array
     */
    public function getZones()
    {
        return $this->configHelper->getZones();
    }
}
