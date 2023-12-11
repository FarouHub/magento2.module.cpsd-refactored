<?php
/**
 * Best4Mage - Best4Mage Configurable Product Simple Details
 * @author Best4Mage
 */
?>
<?php

namespace Lightweight\CpsdRefactored\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\AppInterface;
use Magento\Framework\App\ProductMetadataInterface;

class Data extends AbstractHelper
{

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ProductMetadataInterface
     */
    protected $_productMetaData;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ProductMetadataInterface $productMetaData
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ProductMetadataInterface $productMetaData
    ) {
        $this->_storeManager = $storeManager;
        $this->_productMetaData = $productMetaData;
        parent::__construct($context);
    }

    /**
     * Get current store id.
     */
    public function getStoreId()
    {

        return $this->_storeManager->getStore()->getStoreId();
    }

    /**
     * Check if product level setting is enabled.
     */
    public function isProductLevel()
    {

        return $this->getConfig('product_level', 'general_settings');
    }

    /**
     * Check if module is enabled.
     */
    public function isEnabled($product = null)
    {
        if ($this->getConfig('enable', 'general_settings') && $product!= null && $this->isProductLevel()) {
            return $product->getResource()->getAttributeRawValue($product->getId(), 'cpsd_enable', $this->getStoreId());
        }

        return $this->getConfig('enable', 'general_settings');
    }

    /**
     * Get labels to be updated
     * @return string
     */
    public function getLabelsToUpdate()
    {
        return $this->getConfig('labels_to_update', 'labels_settings');
    }

    /**
     * Get Qty increment message position
     * @return int
     */
    public function getQtyIncPosition()
    {
        return $this->getConfig('qty_inc_pos', 'other_settings');
    }

    /**
     * Get preselect type
     * @param \Magento\Catalog\Model\Product
     * @return int
     */
    public function getPreselectType($product = null)
    {
        if ($product!= null && $this->isProductLevel()) {
            return $product->getResource()->getAttributeRawValue($product->getId(), 'cpsd_preselect_type', $this->getStoreId());
        }
        return $this->getConfig('preselect_type', 'other_settings');
    }

    /**
     * Get preselect type
     * @param \Magento\Catalog\Model\Product
     * @return int
     */
    public function getPreselectOption($product)
    {
        if ($product) {
            return $product->getResource()->getAttributeRawValue($product->getId(), 'cpsd_preselect_specific', $this->getStoreId());
        }
        return false;
    }

    /**
     * Check if cptp is enabled.
     */
    public function isCptpEnable($product = null)
    {
        if ($product!= null && $this->isProductLevel()) {
            return $product->getResource()->getAttributeRawValue($product->getId(), 'cpsd_tp_enable', $this->getStoreId());
        }

        return $this->getConfig('enable_cpsd_tp', 'tp_settings');
    }

    /**
     * Get tier price type
     * @param \Magento\Catalog\Model\Product
     * @return int
     */
    public function getPriceType($product = null)
    {
        if ($product!= null && $this->isProductLevel()) {
            return $product->getResource()->getAttributeRawValue($product->getId(), 'cpsd_tp_price_type', $this->getStoreId());
        }

        return $this->getConfig('price_type', 'tp_settings');
    }

    /**
     * Check if use of common tier price is enabled
     * @param \Magento\Catalog\Model\Product
     * @return boolean
     */
    public function isUseForAll($product = null)
    {
        if ($product!= null && $this->isProductLevel()) {
            return $product->getResource()->getAttributeRawValue($product->getId(), 'cpsd_tp_use_for_all', $this->getStoreId());
        }

        return $this->getConfig('use_for_all', 'tp_settings');
    }

    /**
     * Check if grid look is enabled
     * @return boolean
     */
    public function isEnableGridLook()
    {
        return $this->getConfig('enable_grid_look', 'tp_settings');
    }

    /**
     * Check if qty autofill is enabled
     * @return boolean
     */
    public function isEnableQtyAutofill()
    {
        return $this->getConfig('enable_qty_autofill', 'tp_settings');
    }

    /**
     * Get grid title
     * @return string
     */
    public function getGridTitle()
    {
        return $this->getConfig('grid_title', 'tp_settings');
    }

    /**
     * Check if Category Features are enabled
     * @param \Magento\Catalog\Model\Category | null
     * @return int
     */
    public function isEnableCat($category = null)
    {
        if (!$this->getConfig('enable', 'general_settings')) {
            return false;
        }
        if ($this->getConfig('enable_cat', 'category_settings') && $category !== null && $this->isCategoryLevel()) {
            return $category->getResource()->getAttributeRawValue($category->getId(), 'cpsd_enable', $this->getStoreId());
        }
        return $this->getConfig('enable_cat', 'category_settings');
    }

    /**
     * Check if Category Level option is enabled
     * @return int
     */
    public function isCategoryLevel()
    {
        return $this->getConfig('use_cat_level', 'category_settings');
    }

    /**
     * Check if Price range is enabled
     * @param \Magento\Catalog\Model\Category | null
     * @return int
     */
    public function isShowPriceRange($category)
    {
        if ($category !== null && $this->isCategoryLevel()) {
            return $category->getResource()->getAttributeRawValue($category->getId(), 'cpsd_show_price_range', $this->getStoreId());
        }
        return $this->getConfig('show_price_range', 'category_settings');
    }

    /**
     * Get Range Label
     * @return int
     */
    public function getRangeLabel()
    {
        return $this->getConfig('range_label', 'category_settings');
    }

    /**
     * Check if lowest price should consider tier lowest price
     * @param \Magento\Catalog\Model\Category | null
     * @return int
     */
    public function isUseTierMin($category)
    {
        if ($category !== null && $this->isCategoryLevel()) {
            return $category->getResource()->getAttributeRawValue($category->getId(), 'cpsd_use_tier_min', $this->getStoreId());
        }
        return $this->getConfig('is_use_tier_min', 'category_settings');
    }

    /**
     * Check if price should switch on swatch selection
     * @param \Magento\Catalog\Model\Category | null
     * @return int
     */
    public function isSwitchPrice($category)
    {
        if ($category !== null && $this->isCategoryLevel()) {
            return $category->getResource()->getAttributeRawValue($category->getId(), 'cpsd_switch_price', $this->getStoreId());
        }
        return $this->getConfig('switch_price', 'category_settings');
    }

    /**
     * Check if tier price should show on swatch selection
     * @param \Magento\Catalog\Model\Category | null
     * @return int
     */
    public function isShowTierPrice($category)
    {
        if ($category !== null && $this->isCategoryLevel()) {
            return $category->getResource()->getAttributeRawValue($category->getId(), 'cpsd_show_tier_price', $this->getStoreId());
        }
        return $this->getConfig('show_tier_price', 'category_settings');
    }

    /**
     * Get Tier Price Tooltip Label
     * @return string
     */
    public function getTierLabel()
    {
        return $this->getConfig('tier_label', 'category_settings')
            ? $this->getConfig('tier_label', 'category_settings')
            : __('Offers');
    }

    /**
     * Check if product name should switch on swatch selection
     * @param \Magento\Catalog\Model\Category | null
     * @return int
     */
    public function isSwitchProductName($category)
    {
        if ($category !== null && $this->isCategoryLevel()) {
            return $category->getResource()->getAttributeRawValue($category->getId(), 'cpsd_switch_name', $this->getStoreId());
        }
        return $this->getConfig('switch_name', 'category_settings');
    }

    /**
     * Get Product name selector on list page
     * @return string
     */
    public function getListNameSelector()
    {
        return $this->getConfig('list_name_selector', 'category_settings')
            ? $this->getConfig('list_name_selector', 'category_settings')
            : '.product-item-name > a';
    }

    /**
     * Check if product url should switch on swatch selection
     * @param \Magento\Catalog\Model\Category | null
     * @return int
     */
    public function isSwitchProductUrl($category)
    {
        if ($category !== null && $this->isCategoryLevel()) {
            return $category->getResource()->getAttributeRawValue($category->getId(), 'cpsd_switch_url', $this->getStoreId());
        }
        return $this->getConfig('switch_url', 'category_settings');
    }

    /**
     * Get Product url selector on list page
     * @return string
     */
    public function getListUrlSelector()
    {
        $selectors = $this->getConfig('list_url_selector', 'category_settings')
            ? $this->getConfig('list_url_selector', 'category_settings')
            : '.product-item-link,.product-item-photo,.product-item-description > a';

        $selectors = explode(',', trim($selectors, ','));
        return $selectors;
    }

    /**
     * Get all the selectors
     */
    public function getAllSelectors()
    {
        return $this->scopeConfig->getValue(
            'cpsd/labels_settings',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get system configuration values
     * @param string $field_id
     * @param string $group
     * @param string $section
     * @return int
     */
    public function getConfig($field_id, $group, $section = 'cpsd')
    {
        $config_path = $section . '/' . $group . '/' . $field_id;
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getMagentoVersion()
    {
        if (defined('AppInterface::VERSION')) {
            return AppInterface::VERSION;
        } else {
            return $this->_productMetaData->getVersion();
        }
    }
}
