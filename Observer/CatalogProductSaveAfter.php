<?php
 
namespace Lightweight\CpsdRefactored\Observer;
 
use Magento\Framework\Event\Observer;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Event\ObserverInterface;
use Lightweight\CpsdRefactored\Model\Indexer\SimpleProductUrlRewrites;
 
class CatalogProductSaveAfter implements ObserverInterface
{
    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var SimpleProductUrlRewrites
     */
    private $simpleProductUrlRewrites;
    
    /**
     * @param IndexerRegistry $indexerRegistry
     * @param SimpleProductUrlRewrites $simpleProductUrlRewrites
     */
    public function __construct(IndexerRegistry $indexerRegistry, SimpleProductUrlRewrites $simpleProductUrlRewrites)
    {
        $this->indexerRegistry = $indexerRegistry;
        $this->simpleProductUrlRewrites = $simpleProductUrlRewrites;
    }
    
    /**
     * Executes reindexing for saved product
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
 
        if ($product && $product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $childrenIds = $product->getTypeInstance()->getChildrenIds($product->getId());
            if ($childrenIds && is_array($childrenIds)) {
                foreach ($childrenIds[0] as $cId) {
                    $this->simpleProductUrlRewrites->prepareAndGenerateUrl($cId, $product->getId());
                }
            }
        }
    }
}
