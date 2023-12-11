<?php

namespace Lightweight\CpsdRefactored\Model\Rewrite\Product\Type\Configurable;

use Lightweight\CpsdRefactored\Helper\Data as CPSDHelper;
use Magento\Framework\Registry;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Magento\Backend\Model\Session\Quote as AdminSession;
use Magento\Checkout\Model\Session as FrontSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogRule\Model\ResourceModel\RuleFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Price extends \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Price
{

    /**
     * @var CPSDHelper
     */
    protected $_cpsdHelper;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var State
     */
    protected $_state;

    /**
     * @var AdminSession
     */
    protected $_adminSession;

    /**
     * @var FrontSession
     */
    protected $_frontSession;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * Constructor
     *
     * @param CPSDHelper $cpsdHelper
     * @param Registry $registry
     * @param State $state
     * @param AdminSession $adminSession
     * @param FrontSession $frontSession
     * @param ProductRepositoryInterface $productRepository
     * @param RuleFactory $ruleFactory
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $localeDate
     * @param Session $customerSession
     * @param ManagerInterface $eventManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param GroupManagementInterface $groupManagement
     * @param ProductTierPriceInterfaceFactory $tierPriceFactory
     * @param ScopeConfigInterface $config
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        CPSDHelper $cpsdHelper,
        Registry $registry,
        State $state,
        AdminSession $adminSession,
        FrontSession $frontSession,
        ProductRepositoryInterface $productRepository,
        RuleFactory $ruleFactory,
        StoreManagerInterface $storeManager,
        TimezoneInterface $localeDate,
        Session $customerSession,
        ManagerInterface $eventManager,
        PriceCurrencyInterface $priceCurrency,
        GroupManagementInterface $groupManagement,
        ProductTierPriceInterfaceFactory $tierPriceFactory,
        ScopeConfigInterface $config
    ) {
        $this->_cpsdHelper = $cpsdHelper;
        $this->_registry = $registry;
        $this->_state = $state;
        $this->_adminSession = $adminSession;
        $this->_frontSession = $frontSession;
        $this->_productRepository = $productRepository;

        parent::__construct(
            $ruleFactory,
            $storeManager,
            $localeDate,
            $customerSession,
            $eventManager,
            $priceCurrency,
            $groupManagement,
            $tierPriceFactory,
            $config
        );
    }

    /**
     * Get product final price
     *
     * @param   float $qty
     * @param   \Magento\Catalog\Model\Product $product
     * @return  float
     */
    public function getFinalPrice($qty, $product)
    {
        // get default final price
        $finalPrice = parent::getFinalPrice($qty, $product);
        // if it is product page do not apply cpsd calculation
        if ($this->_registry->registry('current_product')) {
            return $finalPrice;
        }

        if ($this->_cpsdHelper->isCptpEnable($product)) {
            $tierPrice = $this->_calcCptpPricing($product);
            $cpsdTierDisplay = $this->_cpsdHelper->getPriceType($product);
            // Return tier price only if it is lower than final price .
            if ($cpsdTierDisplay == 1 && $tierPrice <= $finalPrice) {
                $finalPrice = $tierPrice;
                $finalPrice = $this->_applyOptionsPrice($product, $qty, $finalPrice);
            } elseif ($cpsdTierDisplay != 1) {
                $finalPrice = $tierPrice;
                $finalPrice = $this->_applyOptionsPrice($product, $qty, $finalPrice);
            }
        }
        return $finalPrice;
    }

    /**
     * Get cpsd final price
     *
     * @param   \Magento\Catalog\Model\Product $product
     * @return  float
     */
    protected function _calcCptpPricing($product)
    {
        $cpsdTierDisplay = $this->_cpsdHelper->getPriceType($product);
        $isUseForAll = $this->_cpsdHelper->isUseForAll($product);

        // get total qty of all simple products in cart
        $totalQty = $this->_getTotalQty($product);
        $tierPrice = parent::getFinalPrice($totalQty, $product);

        $custGroup = parent::_getCustomerGroupId($product);

        if ($isUseForAll) {
            // if common tier price is enabled then use only that price for all simples in cart
            if ($tierProduct = $this->_getTierProduct($product)) {
                $allTierPrices = $tierProduct->getTierPrices($tierProduct);
                if (count($allTierPrices)) {
                    $finalTierPrice = parent::getFinalPrice($totalQty, $product);
                    foreach ($allTierPrices as $key => $tPrice) {
                        if ((
                                $tPrice->getCustomerGroupId() == parent::getAllCustomerGroupsId() ||
                                $tPrice->getCustomerGroupId() == $custGroup
                            ) &&
                            $totalQty >= $tPrice->getQty()
                        ) {
                            $finalTierPrice = $tPrice->getValue();
                        }
                    }
                    return $finalTierPrice;
                }
            }
        } else {
            // if CPSD Tier price type is set to "Price of respective simple product"
            if ($cpsdTierDisplay == 1) {
                return $tierPrice = parent::getFinalPrice($totalQty, $product);
            }
        
            if (($items = $this->_getAllItems()) && !is_null($totalQty)) {
                // to store all the applicable tier prices
                $pTierPrices = [];

                foreach ($items as $item) {
                    // if it is simple product then get applicable tier price
                    if ($item->getParentItem()) {
                        if ($item->getParentItem()->getProductId() == $product->getId()) {
                            $simpleProduct = $item->getProduct();
                            $allTierPrices = $simpleProduct->getTierPrices($simpleProduct);
                            if (count($allTierPrices) > 0) {
                                foreach ($allTierPrices as $key => $tPrice) {
                                    if ((
                                            $tPrice->getCustomerGroupId() == parent::getAllCustomerGroupsId() ||
                                            $tPrice->getCustomerGroupId() == $custGroup
                                        ) &&
                                        $totalQty >= $tPrice->getQty()
                                    ) {
                                        $pTierPrices[$simpleProduct->getId()]['price'] = $tPrice->getValue();
                                    }
                                }
                            }
                        }
                    }
                }

                // if CPSD Tier price type is set to "Highest price from all applied tier prices"
                if (count($pTierPrices) > 0 && $cpsdTierDisplay == 2) {
                    $tierPrice = max(array_map(function ($a) {
                        return $a['price'];
                    }, $pTierPrices));
                }

                // if CPSD Tier price type is set to "Lowest price from all applied tier prices"
                if (count($pTierPrices) > 0 && $cpsdTierDisplay == 3) {
                    $tierPrice = min(array_map(function ($a) {
                        return $a['price'];
                    }, $pTierPrices));
                }
            }
        }
        
        return $tierPrice;
    }


    protected function _getTierProduct($product)
    {
        $loadedProduct = $this->_productRepository->getById($product->getId());
        $tierProductId = $loadedProduct->getCpsdTpTierProduct();
        if ($tierProductId) {
            return $this->_productRepository->getById($tierProductId);
        }
        return false;
    }

    /**
     * Get all visible items from quote
     */
    protected function _getAllItems()
    {
        $items = null;
        try {
            if ($this->_state->getAreaCode() == Area::AREA_ADMIN || $this->_state->getAreaCode() == Area::AREA_ADMINHTML) {
                $items = $this->_adminSession->getQuote()->getAllItems();
            } else {
                $items = $this->_frontSession->getQuote()->getAllItems();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
        }
        return $items;
    }

    /**
     * Get total qty of all simple products in cart
     */
    protected function _getTotalQty($product)
    {
        $totalQty = null;
        if ($items = $this->_getAllItems()) {
            $pQtys = [];

            foreach ($items as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                $pQtys[$item->getProductId()][] = $item->getQty();
            }

            if (array_key_exists($product->getId(), $pQtys)) {
                $totalQty = array_sum($pQtys[$product->getId()]);
            }
        }
        return $totalQty;
    }
}
