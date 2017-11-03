<?php
namespace Firebear\CloudFlare\Model\Config\Source;

class ChallangeTTL implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => 300, 'label' => __('5 minutes')],
            ['value' => 900, 'label' => __('15 minutes')],
            ['value' => 1800, 'label' => __('30 minutes')],
            ['value' => 2700, 'label' => __('45 minutes')],
            ['value' => 3600, 'label' => __('1 hour')],
            ['value' => 7200, 'label' => __('2 hours')],
            ['value' => 10800, 'label' => __('3 hours')],
            ['value' => 14400, 'label' => __('4 hours')],
            ['value' => 28800, 'label' => __('8 hours')],
            ['value' => 57600, 'label' => __('16 hours')],
            ['value' => 86400, 'label' => __('1 day')],
            ['value' => 604800, 'label' => __('1 week')],
            ['value' => 2592000, 'label' => __('1 month')],
            ['value' => 31536000, 'label' => __('1 year')],
        ];
    }
}
