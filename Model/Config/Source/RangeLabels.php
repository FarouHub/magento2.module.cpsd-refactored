<?php

namespace Lightweight\CpsdRefactored\Model\Config\Source;

class RangeLabels extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Retrieve all options array
     * @return array
     */
    public function getAllOptions()
    {

        return [
            [
                'label' => __('None'),
                'value' => 0
            ],
            [
                'label' => __('From ... To ...'),
                'value' => 1
            ],
            [
                'label' => __('Between ... - ...'),
                'value' => 2
            ]
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
                'label' => __('None')
            ],
            [
                'value' => 1,
                'label' => __('From ... To ...')
            ],
            [
                'value' => 2,
                'label' => __('Between ... - ...')
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
            0 => __('None'),
            1 => __('From ... To ...'),
            2 => __('Between ... - ...')
        ];
    }
}
