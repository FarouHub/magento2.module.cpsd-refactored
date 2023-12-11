<?php

namespace Lightweight\CpsdRefactored\Model\Config\Source;

use Magento\Framework\Registry;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Model\ResourceModel\Entity\AttributeFactory;

class Simpleproducts extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var Array
     */
    protected $_allSimpleProducts = null;
    
    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var AttributeFactory
     */
    protected $_eavAttrEntity;

    /**
     * @param Registry $registry
     */
    public function __construct(
        Registry $registry,
        AttributeFactory $eavAttrEntity
    ) {
        $this->_registry = $registry;
        $this->_eavAttrEntity = $eavAttrEntity;
    }


    protected function _getAllSimpleProducts()
    {
        if (is_null($this->_allSimpleProducts)) {
            $currProduct = $this->_registry->registry('product');
            if ($currProduct) {
                if ($currProduct->getTypeId() == Configurable::TYPE_CODE) {
                    $usedProductCollection = $currProduct->getTypeInstance(true)->getUsedProducts($currProduct);
                    if (count($usedProductCollection)) {
                        foreach ($usedProductCollection as $simpleProduct) {
                            $this->_allSimpleProducts[$simpleProduct->getId()] = $simpleProduct->getName();
                        }
                    }
                }
            }
        }
        return $this->_allSimpleProducts;
    }


    /**
     * Retrieve all options array
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = [
            [
                'label' => __('Choose Product...'),
                'value' => ''
            ],
        ];

        if (!is_null($simpleProducts = $this->_getAllSimpleProducts())) {
            foreach ($simpleProducts as $value => $label) {
                array_push(
                    $this->_options,
                    [
                        'label' => $label,
                        'value' => $value
                    ]
                );
            }
        }

        return $this->_options;
    }

    /**
     * Options getter
     * @return array
     */
    public function toOptionArray()
    {
        $return = [
            [
                'value' => '',
                'label' => __('Choose Product...')
            ],
        ];

        if (!is_null($simpleProducts = $this->_getAllSimpleProducts())) {
            foreach ($simpleProducts as $value => $label) {
                array_push(
                    $return,
                    [
                        'value' => $value,
                        'label' => $label
                    ]
                );
            }
        }
        return $return;
    }

    /**
     * Get options in 'key=>value' format
     * @return array
     */
    public function toArray()
    {
        $return = [
            '' => __('Choose Product...')
        ];

        if (!is_null($simpleProducts = $this->_getAllSimpleProducts())) {
            foreach ($simpleProducts as $value => $label) {
                array_push(
                    $return,
                    [
                        $value => $label
                    ]
                );
            }
        }
        return $return;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColumns()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();

        return [
            $attributeCode => [
                'unsigned' => true,
                'default' => null,
                'extra' => null,
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'CPSD Simple product ' . $attributeCode . ' column',
            ],
        ];
    }

    /**
     * Retrieve Select For Flat Attribute update
     *
     * @param int $store
     * @return \Magento\Framework\DB\Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return $this->_eavAttrEntity->create()->getFlatUpdateSelect($this->getAttribute(), $store);
    }

    /**
     * Set attribute instance
     *
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @return $this
     */
    public function setAttribute($attribute)
    {
        $this->_attribute = $attribute;
        return $this;
    }

    /**
     * Get attribute instance
     *
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    public function getAttribute()
    {
        return $this->_attribute;
    }
}
