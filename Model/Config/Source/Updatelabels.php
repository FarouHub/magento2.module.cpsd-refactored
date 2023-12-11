<?php

namespace Lightweight\CpsdRefactored\Model\Config\Source;

class Updatelabels implements \Magento\Framework\Option\ArrayInterface
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
                'value' => '',
                'label' => __('No Selection')
            ],
            [
                'value' => 'history',
                'label' => __('Product Url')
            ],
            [
                'value' => 'name',
                'label' => __('Name')
            ],
            [
                'value' => 'sku',
                'label' => __('SKU')
            ],
            [
                'value' => 'stock',
                'label' => __('Stock Status')
            ],
            [
                'value' => 'min_max_qty',
                'label' => __('Min/Max Sale Qty')
            ],
            [
                'value' => 'qty_increments',
                'label' => __('Qty Increments')
            ],
            [
                'value' => 'short_description',
                'label' => __('Short Description')
            ],
            [
                'value' => 'description',
                'label' => __('Description')
            ],
            [
                'value' => 'attributes',
                'label' => __('More Information')
            ],
            [
                'value' => 'meta_info',
                'label' => __('Meta Information')
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
            ''                  => __('No Selection'),
            'history'           => __('Product Url'),
            'name'              => __('Name'),
            'sku'               => __('SKU'),
            'stock'             => __('Stock Status'),
            'min_max_qty'       => __('Min/Max Sale Qty'),
            'qty_increments'    => __('Qty Increments'),
            'short_description' => __('Short Description'),
            'description'       => __('Description'),
            'attributes'        => __('More Information'),
            'meta_info'         => __('Meta Information')
        ];
    }
}
