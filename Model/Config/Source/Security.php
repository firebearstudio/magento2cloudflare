<?php
namespace Firebear\CloudFlare\Model\Config\Source;

class Security implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'under_attack', 'label' => __('Help! I am under attack!')],
            ['value' => 'high', 'label' => __('Hig')],
            ['value' => 'medium', 'label' => __('Medium')],
            ['value' => 'low', 'label' => __('Low')],
            ['value' => 'essentially_off', 'label' => __('Essentially off')]
        ];
    }
}
