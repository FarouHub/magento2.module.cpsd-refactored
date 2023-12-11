<?php

namespace Lightweight\CpsdRefactored\Model\Config\Source;

class QtyIncPos implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {

        return [
            [
                'value' => 0,
                'label' => __('Default')
            ],
            [
                'value' => 1,
                'label' => __('Before Add To cart button')
            ],
            [
                'value' => 2,
                'label' => __('Before Qty Box')
            ],
            [
                'value' => 3,
                'label' => __('After Product Name')
            ]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {

        return [
            0 => __('Default'),
            1 => __('Before Add To cart button'),
            2 => __('Before Qty Box'),
            3 => __('After Product Name')
        ];
    }
}
