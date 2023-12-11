<?php

namespace Lightweight\CpsdRefactored\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    
    /**
     * @var EavSetupFactory
     */
    private $_eavSetupFactory;

    /**
     * Init
     * @param EavSetupFactory $eavSetupFactory
     * @return void
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->_eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), '1.0.0') < 0) {

            /**
             * Add attributes to the eav/attribute
             */
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'cpsd_enable',
                [
                    'type'                      => 'int',
                    'backend'                   => '',
                    'frontend'                  => '',
                    'label'                     => 'Enable CPSD',
                    'input'                     => 'boolean',
                    'class'                     => '',
                    'source'                    => '',
                    'global'                    => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
                    'visible'                   => true,
                    'required'                  => false,
                    'user_defined'              => true,
                    'default'                   => 0,
                    'searchable'                => false,
                    'filterable'                => false,
                    'comparable'                => false,
                    'visible_on_front'          => false,
                    'used_in_product_listing'   => true,
                    'apply_to'                  => Configurable::TYPE_CODE,
                    'group'                     => 'Best4Mage CPSD Settings'
                ]
            );
            
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'cpsd_preselect_type',
                [
                    'type'                      => 'int',
                    'backend'                   => '',
                    'frontend'                  => '',
                    'label'                     => 'Type of Preselection',
                    'input'                     => 'select',
                    'class'                     => '',
                    'source'                    => 'Lightweight\CpsdRefactored\Model\Config\Source\PreselectType',
                    'global'                    => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
                    'visible'                   => true,
                    'required'                  => false,
                    'user_defined'              => true,
                    'default'                   => 0,
                    'searchable'                => false,
                    'filterable'                => false,
                    'comparable'                => false,
                    'visible_on_front'          => false,
                    'used_in_product_listing'   => true,
                    'apply_to'                  => Configurable::TYPE_CODE,
                    'group'                     => 'Best4Mage CPSD Settings',
                    'note'                      => __('This will auto select options on product details page. Lowest price option will consider price of associated products.')
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'cpsd_preselect_specific',
                [
                    'type'                      => 'varchar',
                    'backend'                   => '',
                    'frontend'                  => '',
                    'label'                     => 'Select options to preselect',
                    'input'                     => 'select',
                    'class'                     => '',
                    'source'                    => 'Lightweight\CpsdRefactored\Model\Config\Source\PreselectOptions',
                    'global'                    => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
                    'visible'                   => true,
                    'required'                  => false,
                    'user_defined'              => true,
                    'default'                   => 0,
                    'searchable'                => false,
                    'filterable'                => false,
                    'comparable'                => false,
                    'visible_on_front'          => false,
                    'used_in_product_listing'   => true,
                    'apply_to'                  => Configurable::TYPE_CODE,
                    'group'                     => 'Best4Mage CPSD Settings'
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'cpsd_tp_enable',
                [
                    'type'                      => 'int',
                    'backend'                   => '',
                    'frontend'                  => '',
                    'label'                     => 'Enable Configurable Product Tier Pricing',
                    'input'                     => 'boolean',
                    'class'                     => '',
                    'source'                    => '',
                    'global'                    => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
                    'visible'                   => true,
                    'required'                  => false,
                    'user_defined'              => true,
                    'default'                   => 0,
                    'searchable'                => false,
                    'filterable'                => false,
                    'comparable'                => false,
                    'visible_on_front'          => false,
                    'used_in_product_listing'   => true,
                    'apply_to'                  => Configurable::TYPE_CODE,
                    'group'                     => 'Best4Mage CPSD Settings'
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'cpsd_tp_use_for_all',
                [
                    'type'                      => 'int',
                    'backend'                   => '',
                    'frontend'                  => '',
                    'label'                     => 'Use specific tier price for all',
                    'input'                     => 'boolean',
                    'class'                     => '',
                    'source'                    => '',
                    'global'                    => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
                    'visible'                   => true,
                    'required'                  => false,
                    'user_defined'              => true,
                    'default'                   => 0,
                    'searchable'                => false,
                    'filterable'                => false,
                    'comparable'                => false,
                    'visible_on_front'          => false,
                    'used_in_product_listing'   => true,
                    'apply_to'                  => Configurable::TYPE_CODE,
                    'group'                     => 'Best4Mage CPSD Settings',
                    'note'                      => 'Enable this option if you want to use one simple product\'s tier price for all other simple products.'
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'cpsd_tp_tier_product',
                [
                    'type'                      => 'int',
                    'backend'                   => '',
                    'frontend'                  => '',
                    'label'                     => 'Choose tier price product',
                    'input'                     => 'select',
                    'class'                     => '',
                    'source'                    => 'Lightweight\CpsdRefactored\Model\Config\Source\Simpleproducts',
                    'global'                    => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
                    'visible'                   => true,
                    'required'                  => false,
                    'user_defined'              => true,
                    'default'                   => 0,
                    'searchable'                => false,
                    'filterable'                => false,
                    'comparable'                => false,
                    'visible_on_front'          => false,
                    'used_in_product_listing'   => true,
                    'apply_to'                  => Configurable::TYPE_CODE,
                    'group'                     => 'Best4Mage CPSD Settings',
                    'note'                      => 'Select a simple product from which tier price should be used for all other simple products.'
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'cpsd_tp_price_type',
                [
                    'type'                      => 'int',
                    'backend'                   => '',
                    'frontend'                  => '',
                    'label'                     => 'Which tier price should be used?',
                    'input'                     => 'select',
                    'class'                     => '',
                    'source'                    => 'Lightweight\CpsdRefactored\Model\Config\Source\Priceoptions',
                    'global'                    => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
                    'visible'                   => true,
                    'required'                  => false,
                    'user_defined'              => true,
                    'default'                   => 0,
                    'searchable'                => false,
                    'filterable'                => false,
                    'comparable'                => false,
                    'visible_on_front'          => false,
                    'used_in_product_listing'   => true,
                    'apply_to'                  => Configurable::TYPE_CODE,
                    'group'                     => 'Best4Mage CPSD Settings',
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                'cpsd_enable',
                [
                    'type'     => 'int',
                    'label'    => 'Enable CPSD',
                    'input'    => 'boolean',
                    'source'   => '',
                    'visible'  => true,
                    'default'  => '0',
                    'required' => false,
                    'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group'    => 'Best4Mage CPSD Settings',
                    'note'     => ''
                ]
            );
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                'cpsd_show_price_range',
                [
                    'type'     => 'int',
                    'label'    => 'Show Price Range',
                    'input'    => 'boolean',
                    'source'   => '',
                    'visible'  => true,
                    'default'  => '0',
                    'required' => false,
                    'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group'    => 'Best4Mage CPSD Settings',
                    'note'     => ''
                ]
            );
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                'cpsd_use_tier_min',
                [
                    'type'     => 'int',
                    'label'    => 'Use Tier Price to Show Lowest Price',
                    'input'    => 'boolean',
                    'source'   => '',
                    'visible'  => true,
                    'default'  => '0',
                    'required' => false,
                    'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group'    => 'Best4Mage CPSD Settings',
                    'note'     => 'This option will allow to consider tier price for lowest amount in the default price display or in price range display.'
                ]
            );
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                'cpsd_switch_price',
                [
                    'type'     => 'int',
                    'label'    => 'Show Price for Selected Options',
                    'input'    => 'boolean',
                    'source'   => '',
                    'visible'  => true,
                    'default'  => '0',
                    'required' => false,
                    'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group'    => 'Best4Mage CPSD Settings',
                    'note'     => 'Note: This option will only work if you have swatches visible on category pages.'
                ]
            );
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                'cpsd_show_tier_price',
                [
                    'type'     => 'int',
                    'label'    => 'Show Tier Price for Selected Options',
                    'input'    => 'boolean',
                    'source'   => '',
                    'visible'  => true,
                    'default'  => '0',
                    'required' => false,
                    'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group'    => 'Best4Mage CPSD Settings',
                    'note'     => 'Note: This option will only work if you have swatches visible on category pages. This option will allow you to show tier prices as a tooltip on category page.'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                'cpsd_switch_name',
                [
                    'type'     => 'int',
                    'label'    => 'Switch Product Name',
                    'input'    => 'boolean',
                    'source'   => '',
                    'visible'  => true,
                    'default'  => '0',
                    'required' => false,
                    'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group'    => 'Best4Mage CPSD Settings',
                    'note'     => 'Note: This option will only work if you have swatches visible on category pages. This option will allow to to switch Product name on swatch selection.'
                ]
            );
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                'cpsd_switch_url',
                [
                    'type'     => 'int',
                    'label'    => 'Switch Product Url',
                    'input'    => 'boolean',
                    'source'   => '',
                    'visible'  => true,
                    'default'  => '0',
                    'required' => false,
                    'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group'    => 'Best4Mage CPSD Settings',
                    'note'     => 'Note: This option will only work if you have swatches visible on category pages. This option will allow to to switch Product url on swatch selection.'
                ]
            );
        }
    }
}
