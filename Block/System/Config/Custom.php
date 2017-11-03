<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Firebear\CloudFlare\Block\System\Config;

use \Magento\Config\Block\System\Config\Form\Field;
use \Firebear\CloudFlare\Helper\Scope;
use \Firebear\CloudFlare\Helper\Config;
use \Magento\Backend\Block\Template\Context;

/**
 * "Reset to Defaults" button renderer
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Custom extends Field
{

    /**
     * @var Scope
     */
    protected $internScope;

    /**
     * @var Config
     */
    protected $configHelper;

    public function __construct(
        Context $context,
        Scope $internScope,
        Config $configHelper
    ) {
        $this->internScope = $internScope;
        $this->configHelper = $configHelper;
        parent::__construct($context);
    }

    public function getDnsRecords()
    {
        $result = [];
        try {
            $zone = $this->configHelper->getZone();
            $zoneDnsClient = $this->configHelper->getDnsConnection();
            $domainName = $this->configHelper->getDomain();
            $records = $zoneDnsClient->list_records($zone['id'], 'A', null, null, 1, 100, 'name', 'asc');
            $result = [];
            if (!isset($records['result']['errors'])) {
                foreach ($records['result'] as $record) {

                    // Skip current domain name
                    if (strcmp($record['name'], $domainName) !== 0) {
                        $code = str_replace('.' . $domainName, '', $record['name']);

                        // Skip wildcards
                        if (strpos($code, '*') === false) {
                            $result[$code] = $record['name'];
                        }
                    }
                }
            }
        } catch (\Exception $e) {

        }
        
        return $result;
    }

    /**
     * Return element html
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setData('template', null);
        $this->setData('additional_key', null);

        $originalData = $element->getOriginalData();
        if($originalData['template']) {
            $this->setTemplate($originalData['template']);
            $this->setElement($element);
        }

        $this->setData('data-name', $originalData['id']);

        if(isset($originalData['additionalKey'])) {
            $this->setData('additional_key', $originalData['additionalKey']);
        }

        return $this->_toHtml();
    }
}
