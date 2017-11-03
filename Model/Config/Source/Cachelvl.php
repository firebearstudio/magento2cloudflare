<?php
namespace Firebear\CloudFlare\Model\Config\Source;

class Cachelvl implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'basic', 'label' => __('No Query String')],
            ['value' => 'simplified', 'label' => __('Ignore Query String')],
            ['value' => 'aggressive', 'label' => __('Standard')]
        ];
    }
}
