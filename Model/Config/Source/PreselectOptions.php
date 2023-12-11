<?php

namespace Lightweight\CpsdRefactored\Model\Config\Source;

use Magento\Framework\Registry;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Model\ResourceModel\Entity\AttributeFactory;

class PreselectOptions extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var Array
     */
    protected $_allOptions = null;
    
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


    protected function _getAllAttrOptions()
    {
        if (is_null($this->_allOptions)) {
            $currProduct = $this->_registry->registry('product');
            if ($currProduct) {
                if ($currProduct->getTypeId() == Configurable::TYPE_CODE) {
                    $usedAttr = $currProduct->getTypeInstance(true)->getUsedProductAttributes($currProduct);
                    $attrCode = [];
                    foreach ($usedAttr as $key => $attr) {
                        $attrCode[$key] = $attr->getAttributeCode();
                    }
                    $usedProductCollection = $currProduct->getTypeInstance(true)->getUsedProducts($currProduct);
                    if (count($usedProductCollection)) {
                        foreach ($usedProductCollection as $simpleProduct) {
                            $optionsKey = '';
                            $optionsVal = '';
                            foreach ($attrCode as $key => $code) {
                                $op = $simpleProduct->getAttributeText($code);
                                $optionsVal .= ' - '.$op;
                                $op = str_replace(' ', '-', strtolower($op));
                                $optionsKey .= ':'.$op;
                            }
                            $this->_allOptions[$simpleProduct->getId()] = trim($optionsVal, ' - ');
                        }
                    }
                }
            }
        }
        return $this->_allOptions;
    }


    /**
     * Retrieve all options array
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = [
            [
                'label' => __('--Choose Options--'),
                'value' => ''
            ],
        ];

        if (!is_null($simpleProducts = $this->_getAllAttrOptions())) {
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
                'label' => __('--Choose Options--')
            ],
        ];

        if (!is_null($simpleProducts = $this->_getAllAttrOptions())) {
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
            '' => __('--Choose Options--')
        ];

        if (!is_null($simpleProducts = $this->_getAllAttrOptions())) {
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
