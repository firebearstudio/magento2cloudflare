<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Firebear\CloudFlare\Block\System\Config\Fieldset;

/**
 * Renderer for PayPal banner in System Configuration
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Js extends \Magento\Backend\Block\Template implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @var string
     */
    protected $_template = 'Firebear_CloudFlare::system/config/fieldset/js.phtml';

    /**
     * @var \Firebear\CloudFlare\Helper\Scope
     */
    protected $internScope;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Firebear\CloudFlare\Helper\Scope $internScope,
        array $data = []
    ) {
        $this->internScope = $internScope;
        parent::__construct($context, $data);
    }

    /**
     * Render fieldset html
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $elementOriginalData = $element->getOriginalData();
        $valueScope = $this->internScope->getCurrentScope();
        $scopeType = $valueScope[0];
        $scopeCode = $valueScope[1];
        $this->setScopeType($scopeType);
        $this->setScopeCode($scopeCode);
        return $this->toHtml();
    }
}
