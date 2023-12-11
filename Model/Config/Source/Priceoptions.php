<?php

namespace Lightweight\CpsdRefactored\Model\Config\Source;

class Priceoptions extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Retrieve all options array
     * @return array
     */
    public function getAllOptions()
    {

        $this->_options = [
            [
                'label' => __('Price of respective simple product'),
                'value' => 1
            ],
            [
                'label' => __('Highest price from all applied tier prices'),
                'value' => 2
            ],
            [
                'label' => __('Lowest price from all applied tier prices'),
                'value' => 3
            ],
        ];

        return $this->_options;
    }
    
    /**
     * Options getter
     * @return array
     */
    public function toOptionArray()
    {

        return [
            [
                'value' => 1,
                'label' => __('Price of respective simple product')
            ],
            [
                'value' => 2,
                'label' => __('Highest price from all applied tier prices')
            ],
            [
                'value' => 3,
                'label' => __('Lowest price from all applied tier prices')
            ],
        ];
    }

    /**
     * Get options in "key-value" format
     * @return array
     */
    public function toArray()
    {

        return [
            1 => __('Price of respective simple product'),
            2 => __('Highest price from all applied tier prices'),
            3 => __('Lowest price from all applied tier prices'),
        ];
    }
}
