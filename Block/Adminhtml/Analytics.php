<?php
/**
 * Copyright © 2016 Firebear Studio. All rights reserved.
 */
namespace Firebear\CloudFlare\Block\Adminhtml;

use Magento\Framework\View\Element\Template;
use \Firebear\CloudFlare\Helper\Config;
use \Magento\Directory\Model\Country;
use \Magento\Directory\Model\CountryFactory;

/**
 * Class Analytics.
 *
 * @package Firebear\CloudFlare\Block\Adminhtml
 */
class Analytics extends Template
{
    /**
     * CloudFlare Config helper.
     *
     * @var configHelper
     */
    protected $configHelper;

    /**
     * Default since value.
     *
     * @var string
     */
    protected $since = '-10080';

    /**
     * Country factory.
     *
     * @var Country
     */
    protected $country;

    /**
     * Current domain.
     *
     * @var mixed
     */
    protected $domain;

    /**
     * Analytics constructor.
     *
     * @param Template\Context $context
     * @param Config           $configHelper
     * @param CountryFactory   $countryFactory
     * @param array            $data
     */
    public function __construct(
        Template\Context $context,
        Config $configHelper,
        CountryFactory $countryFactory,
        array $data = []
    ) {
        $this->configHelper = $configHelper;
        $this->country = $countryFactory->create();
        parent::__construct($context, $data);

        if ($since = $this->getRequest()->getParam('since')) {
            $this->setSince($since);
        }

        if ($domain = $this->getRequest()->getParam('domain')) {
            $this->domain = $domain;
        } else {
            $zones = $this->configHelper->getZones();
            foreach ($zones as $zone) {
                $this->domain = $zone['domain'];
                break;
            }
        }
    }

    /**
     * Get CloudFlare Config helper.
     * 
     * @return configHelper|Config
     */
    public function getConfig()
    {
        return $this->configHelper;
    }

    /**
     * Get since.
     *
     * @return string
     */
    public function getSince()
    {
        return $this->since;
    }

    /**
     * Get current scope.
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }
    
    /**
     * Retrieve CloudFlare analytics data.
     *
     * @return array
     */
    public function getCloudflareData()
    {
        $result = [];
        try {
            $result = $this->configHelper->getDashboard($this->getDomain(), $this->getSince());
        } catch (\Exception $e) {
            
        }
        
        return $result;
    }

    /**
     * Retrieve country name by code.
     * 
     * @param $code
     *
     * @return string
     */
    public function getCountryName($code)
    {
        $country = $this->country->loadByCode($code);
        return $country->getName();
    }

    /**
     * Set analytics since parameter.
     *
     * @param $newSince string
     *
     * @return $this
     */
    public function setSince($newSince)
    {
        $sinceArray = $this->getConfig()->getAnalyticsSinceAsArray();
        if (isset($sinceArray[$newSince])) {
            $this->since = $newSince;
        }
        return $this;
    }
}
