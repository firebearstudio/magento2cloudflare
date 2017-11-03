<?php
namespace Firebear\CloudFlare\Model\Config\Source;

class Onoff implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'off', 'label' => __('Off')],
            ['value' => 'on', 'label' => __('On')]
        ];
    }
}
