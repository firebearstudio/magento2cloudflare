<?php
namespace Firebear\CloudFlare\Model\Config\Source;

class BrowserTTL implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => 30, 'label' => __('30 seconds')],
            ['value' => 60, 'label' => __('1 minute')],
            ['value' => 300, 'label' => __('5 minutes')],
            ['value' => 1200, 'label' => __('20 minutes')],
            ['value' => 1800, 'label' => __('30 minutes')],
            ['value' => 3600, 'label' => __('1 hour')],
            ['value' => 7200, 'label' => __('2 hours')],
            ['value' => 10800, 'label' => __('3 hours')],
            ['value' => 14400, 'label' => __('4 hours')],
            ['value' => 18000, 'label' => __('5 hours')],
            ['value' => 28800, 'label' => __('8 hours')],
            ['value' => 43200, 'label' => __('12 hours')],
            ['value' => 57600, 'label' => __('16 hours')],
            ['value' => 72000, 'label' => __('20 hours')],
            ['value' => 86400, 'label' => __('1 day')],
            ['value' => 172800, 'label' => __('2 days')],
            ['value' => 259200, 'label' => __('3 days')],
            ['value' => 345600, 'label' => __('4 days')],
            ['value' => 432000, 'label' => __('5 days')],
            ['value' => 691200, 'label' => __('8 days')],
            ['value' => 1382400, 'label' => __('16 days')],
            ['value' => 2073600, 'label' => __('24 days')],
            ['value' => 2678400, 'label' => __('1 month')],
            ['value' => 5356800, 'label' => __('2 months')],
            ['value' => 16070400, 'label' => __('6 months')],
            ['value' => 31536000, 'label' => __('1 year')],
        ];
    }
}
