<?php

namespace Lightweight\CpsdRefactored\Model\Config\Source;

class PreselectType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Retrieve all options array
     * @return array
     */
    public function getAllOptions()
    {

        return [
            [
                'label' => __('No Preselection'),
                'value' => 0
            ],
            [
                'label' => __('Lowest price product options'),
                'value' => 1
            ],
            [
                'label' => __('Highest price product options'),
                'value' => 2
            ],
            [
                'label' => __('Specific values set at product level'),
                'value' => 3
            ],
            [
                'label' => __('First option in attribute(s)'),
                'value' => 4
            ],
        ];
    }

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
                'label' => __('No Preselection')
            ],
            [
                'value' => 1,
                'label' => __('Lowest price product options')
            ],
            [
                'value' => 2,
                'label' => __('Highest price product options')
            ],
            [
                'value' => 3,
                'label' => __('Specific values set at product level')
            ],
            [
                'value' => 4,
                'label' => __('First option in attribute(s)')
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
            0 => __('No Preselection'),
            1 => __('Lowest price product options'),
            2 => __('Highest price product options'),
            3 => __('Specific values set at product level'),
            4 => __('First option in attribute(s)'),
        ];
    }
}
