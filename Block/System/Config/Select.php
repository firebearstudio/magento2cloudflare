<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Firebear\CloudFlare\Block\System\Config;

use \Magento\Config\Block\System\Config\Form\Field;
use \Magento\Backend\Block\Template\Context;

/**
 * "Reset to Defaults" button renderer
 *
 * @author     Firebear Studio <fbeardev@gmail.com>
 */
class Select extends Field
{
    /**
     * Select constructor.
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }


    /**
     * Return element html
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * 
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        //        var_export($originalData);
        //        $element->addCustomAttribute('data-save-url', $originalData['save_url']);
        //        $element->addCustomAttribute('onChange', $originalData['onChange']);
        $element->addCustomAttribute('data-name', $originalData['id']);
        return parent::_getElementHtml($element);
    }
}
