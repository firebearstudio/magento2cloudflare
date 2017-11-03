<?php
/**
 * Copyright © 2016 Firebear Studio. All rights reserved.
 */

namespace Firebear\CloudFlare\Block\System\Config\Button;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * "Reset to Defaults" button renderer
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class CacheURLButton extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Path to block template
     */
    const CHECK_TEMPLATE = 'system/config/button/cacheurl.phtml';
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
    }
    /**
     * Set template to itself
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::CHECK_TEMPLATE);
        }
        return $this;
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
     * Get the button and scripts contents
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
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
                'api_cacheurl' => $originalData['api_cacheurl'],
                'intern_url' => $this->getUrl($originalData['button_url']),
                'html_id' => $element->getHtmlId(),
                'scope_type' => $scopeType,
                'scope_code' => $scopeCode,
            ]
        );
        return $this->_toHtml();
    }

}
