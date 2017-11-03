<?php
/**
 * Copyright © 2016 Firebear Studio. All rights reserved.
 */
namespace Firebear\CloudFlare\Block\System\Config\Button;

/**
 * "Reset to Defaults" button renderer
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Blacklist extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * @var \Firebear\CloudFlare\Helper\Scope
     */
    protected $internScope;

    /**
     * Blacklist constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Firebear\CloudFlare\Helper\Scope       $internScope
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Firebear\CloudFlare\Helper\Scope $internScope
    ) {
        $this->internScope = $internScope;
        parent::__construct($context);
        $this->setTemplate('system/config/button/blacklist.phtml');
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
