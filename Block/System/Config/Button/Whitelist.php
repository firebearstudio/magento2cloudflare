<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Firebear\CloudFlare\Block\System\Config\Button;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * "Reset to Defaults" button renderer
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Whitelist extends \Magento\Config\Block\System\Config\Form\Field
{


    /**
     * @var \Firebear\CloudFlare\Helper\Scope
     */
    protected $internScope;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Firebear\CloudFlare\Helper\Scope $internScope
    ) {
        $this->internScope = $internScope;
        parent::__construct($context);
        $this->setTemplate('system/config/button/whitelist.phtml');
    }

    /**
     * Render button
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
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
        $originalData = $element->getOriginalData();
       $valueScope = $this->internScope->getCurrentScope();
       $scopeType = $valueScope[0];
       $scopeCode = $valueScope[1];
        $this->addData(
            [
                'button_label' => __($originalData['button_label']),
                'api_ip' => $originalData['api_ip'],
                'intern_url' => $this->getUrl($originalData['button_url']),
                'html_id' => $element->getHtmlId(),
                'scope_type' => $scopeType,
                'scope_code' => $scopeCode,
            ]
        );
        return $this->_toHtml();
    }
}
