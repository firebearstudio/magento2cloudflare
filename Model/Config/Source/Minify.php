<?php
namespace Firebear\CloudFlare\Model\Config\Source;

class Minify implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'off-off-off', 'label' => __('Off')],
            ['value' => 'on-off-off', 'label' => __('JavaScript only')],
            ['value' => 'off-on-off', 'label' => __('CSS only')],
            ['value' => 'on-on-off', 'label' => __('JavaScript and CSS')],
            ['value' => 'off-ono-on', 'label' => __('HTML only')],
            ['value' => 'on-off-on', 'label' => __('JavaScript and HTML')],
            ['value' => 'off-on-on', 'label' => __('CSS and HTML')],
            ['value' => 'on-on-on', 'label' => __('CSS, JavaScript, and HTML')],
        ];
    }
}
