<?php
namespace Lightweight\CpsdRefactored\Plugin\Product;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Block\Product\ListProduct as ListProductBlock;

class ListProduct
{
    /**
     * @var ListProductBlock
     */
    protected $listProductBlock;

    /**
     * @var Configurable
     */
    protected $configurableProduct;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var \Lightweight\CpsdRefactored\Helper\Data
     */
    protected $cpsdHelper;

    /**
     * ListProduct constructor.
     *
     * @param Configurable $configurableProduct
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Lightweight\CpsdRefactored\Helper\Data $cpsdHelper
     */
    public function __construct(
        Configurable $configurableProduct,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Lightweight\CpsdRefactored\Helper\Data $cpsdHelper
    ) {
        $this->configurableProduct = $configurableProduct;
        $this->pricingHelper = $pricingHelper;
        $this->cpsdHelper = $cpsdHelper;
    }

    /**
     * @param ListProductBlock $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function aroundGetProductPrice(
        ListProductBlock $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Product $product
    ) {
        $isEnableCat = $this->cpsdHelper->isEnableCat($subject->getLayer()->getCurrentCategory());
        $isShowPriceRange = $this->cpsdHelper->isShowPriceRange($subject->getLayer()->getCurrentCategory());
        $isSwitchPrice = $this->cpsdHelper->isSwitchPrice($subject->getLayer()->getCurrentCategory());
        $isShowTierPrice = $this->cpsdHelper->isShowTierPrice($subject->getLayer()->getCurrentCategory());
        if ((Configurable::TYPE_CODE !== $product->getTypeId())
            || (Configurable::TYPE_CODE === $product->getTypeId() && (!$isEnableCat || (!$isShowPriceRange && !$isSwitchPrice && !$isShowTierPrice)))
        ) {
            return $proceed($product);
        }
        $defaultPriceHtml = '';
        if (!$isShowPriceRange) {
            $defaultPriceHtml = $proceed($product);
        }

        $this->listProductBlock = $subject;
        $priceText = $this->getPriceRange($product);

        return $defaultPriceHtml.$priceText;
    }

    /**
     * Get configurable product price range
     *
     * @param $product
     * @return string
     */
    public function getPriceRange($product)
    {
        $childProductPrice = [];
        $childTierPrices = [];
        $childProducts = $this->configurableProduct->getUsedProducts($product);
        foreach ($childProducts as $child) {
            $price = number_format($child->getPrice(), 2, '.', '');
            $finalPrice = number_format($child->getFinalPrice(), 2, '.', '');
            if ($price == $finalPrice) {
                $childProductPrice[] = $price;
            } elseif ($finalPrice < $price) {
                $childProductPrice[] = $finalPrice;
            }
        }

        $max = $this->pricingHelper->currencyByStore(max($childProductPrice));
        $min = $this->pricingHelper->currencyByStore(min($childProductPrice));

        if ($this->cpsdHelper->isUseTierMin($this->listProductBlock->getLayer()->getCurrentCategory())) {
            foreach ($childProducts as $child) {
                $priceInfo = $child->getPriceInfo();
                $tierPriceModel = $priceInfo->getPrice('tier_price');
                $tierPricesList = $tierPriceModel->getTierPriceList();
                foreach ($tierPricesList as $tierPrice) {
                    $childTierPrices[] = $tierPrice['price']->getValue();
                }
            }
            if (count($childTierPrices) && (min($childTierPrices) < min($childProductPrice))) {
                $min = $this->pricingHelper->currencyByStore(min($childTierPrices));
            }
        }

        if ($min==$max) {
            return $this->getPriceHtml($product, "$min");
        } else {
            $rangeLabel = $this->cpsdHelper->getRangeLabel();
            if ($rangeLabel == 1) {
                return $this->getPriceHtml($product, "$min", "$max", __('From'), __('To'));
            } elseif ($rangeLabel == 2) {
                return $this->getPriceHtml($product, "$min", "$max", __('Between'), '-');
            } else {
                return $this->getPriceHtml($product, "$min", "$max", '-');
            }
        }
    }

    /**
     * Price renderer
     *
     * @param $product
     * @param $price
     * @return mixed
     */
    protected function getPriceHtml($product, $min, $max = '', $minLabel = '', $maxLabel = '')
    {
        return $this->listProductBlock->getLayout()->createBlock('Magento\Framework\View\Element\Template')
            ->setTemplate('Lightweight_CpsdRefactored::category/price-range.phtml')
            ->setData('price_id_from', 'product-price-from-'.$product->getId())
            ->setData('price_id_to', 'product-price-to-'.$product->getId())
            ->setData('display_label_from', $minLabel)
            ->setData('display_label_to', $maxLabel)
            ->setData('product_id', $product->getId())
            ->setData('display_value_from', $min)
            ->setData('display_value_to', $max)
            ->setData('is_show_price_range', $this->cpsdHelper->isShowPriceRange($this->listProductBlock->getLayer()->getCurrentCategory()))
            ->setData('is_switch_price', $this->cpsdHelper->isSwitchPrice($this->listProductBlock->getLayer()->getCurrentCategory()))
            ->setData('is_show_tier_price', $this->cpsdHelper->isShowTierPrice($this->listProductBlock->getLayer()->getCurrentCategory()))
            ->setData('tier_label', $this->cpsdHelper->getTierLabel())
            ->toHtml();
    }
}
