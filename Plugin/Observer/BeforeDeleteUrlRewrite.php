<?php

namespace Lightweight\CpsdRefactored\Plugin\Observer;

use Magento\Framework\Event\Observer;
use Magento\CatalogUrlRewrite\Observer\ProductProcessUrlRewriteRemovingObserver;
use Lightweight\CpsdRefactored\Model\Indexer\SimpleProductUrlRewrites;

class BeforeDeleteUrlRewrite
{
    /**
     * @var SimpleProductUrlRewrites
     */
    private $simpleProductUrlRewrites;

    /**
     * @param SimpleProductUrlRewrites $simpleProductUrlRewrites
     */
    public function __construct(
        SimpleProductUrlRewrites $simpleProductUrlRewrites
    ) {
        $this->simpleProductUrlRewrites = $simpleProductUrlRewrites;
    }

    /**
     * Remove product urls from storage
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function beforeExecute(ProductProcessUrlRewriteRemovingObserver $subject, Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
 
        if ($product && $product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $childrenIds = $product->getTypeInstance()->getChildrenIds($product->getId());
            if ($childrenIds && is_array($childrenIds)) {
                foreach ($childrenIds[0] as $cId) {
                    $this->simpleProductUrlRewrites->removeChildUrls($cId, $product->getId());
                }
            }
        }
    }
}
