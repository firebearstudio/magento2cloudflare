<?php
namespace Firebear\CloudFlare\Model\Config\Source;

class Async implements \Magento\Framework\Option\ArrayInterface
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
            ['value' => 'on', 'label' => __('Automatic')],
            ['value' => 'manual', 'label' => __('Manual')]
        ];
    }
}
