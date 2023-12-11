<?php
namespace Lightweight\CpsdRefactored\Model\Indexer;

use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory as UrlRewriteService;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;
use Magento\UrlRewrite\Model\OptionProvider;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\ResourceConnection;

class SimpleProductUrlRewrites implements IndexerActionInterface, MviewActionInterface
{


    /**
     * @var StoreManagerInterface
     */
    protected $_storeManagerInterface;

    /**
     * @var UrlPersistInterface
     */
    protected $_urlPersist;

    /**
     * @var UrlRewriteFactory
     */
    protected $_urlRewriteFactory;

    /**
     * @var UrlRewriteService
     */
    protected $_urlRewriteService;

    /**
     * @var UrlFinderInterface
     */
    protected $_urlFinder;

    /**
     * @var ConfigurableType
     */
    protected $_configurableType;

    /**
     * @var ProductRepository
     */
    protected $_productRepository;

    /**
     * @var Resource
     */
    private $_resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $_connection;

    protected $_writer;

    protected $_logger;

    protected $_allSuperLinks = null;

    protected $_productAttributes = [];

    /**
     * Initialize Indexer
     *
     * @param StoreManagerInterface $storeManagerInterface
     * @param UrlPersistInterface $urlPersist
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param UrlRewriteService $urlRewriteService
     * @param UrlFinderInterface $urlFinder
     * @param ConfigurableType $configurableType
     * @param ProductRepository $productRepository
     * @param ResourceConnection $resource
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        UrlPersistInterface $urlPersist,
        UrlRewriteFactory $urlRewriteFactory,
        UrlRewriteService $urlRewriteService,
        UrlFinderInterface $urlFinder,
        ConfigurableType $configurableType,
        ProductRepository $productRepository,
        ResourceConnection $resource
    ) {
        $this->_storeManagerInterface = $storeManager;
        $this->_urlPersist = $urlPersist;
        $this->_urlRewriteFactory = $urlRewriteFactory;
        $this->_urlRewriteService = $urlRewriteService;
        $this->_urlFinder = $urlFinder;
        $this->_configurableType = $configurableType;
        $this->_productRepository = $productRepository;
        $this->_resource = $resource;
        $this->_connection = $resource->getConnection();

        $this->_writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cpsd.log');
        $this->_logger = new \Zend\Log\Logger();
        $this->_logger->addWriter($this->_writer);
    }

    /**
     * Log exception in cpsd.log file.
     */
    protected function _logResults($data)
    {
        $this->_logger->info('----------------------------- Best4Mage CPSD :: START -----------------------------');
        $this->_logger->info(print_r($data, true));
        $this->_logger->info('----------------------------- Best4Mage CPSD :: END -----------------------------');
    }

    /*
     * Used by mview, allows process indexer in the "Update on schedule" mode
     */
    public function execute($ids)
    {
        //Used by mview, allows you to process multiple products in the "Update on schedule" mode
        $allSuperLinks = $this->_getAllSuperLinks($ids);
        if ($allSuperLinks && is_array($allSuperLinks) && count($allSuperLinks)) {
            foreach ($allSuperLinks as $superLink) {
                try {
                    $this->prepareAndGenerateUrl($superLink['product_id'], $superLink['parent_id']);
                } catch (\Exception $e) {
                    $this->_logResults('Something went wrong while executing index! More Details :: '.$e->getMessage());
                }
            }
        }
    }

    /*
     * Will take all of the data and reindex
     * Will run when reindex via command line
     */
    public function executeFull()
    {
        //Should take into account all products in the system
        $allSuperLinks = $this->_getAllSuperLinks();
        if ($allSuperLinks && is_array($allSuperLinks) && count($allSuperLinks)) {
            foreach ($allSuperLinks as $superLink) {
                try {
                    $this->prepareAndGenerateUrl($superLink['product_id'], $superLink['parent_id']);
                } catch (\Exception $e) {
                    $this->_logResults('Something went wrong while executing index! More Details :: '.$e->getMessage());
                }
            }
        }
    }


    /*
     * Works with a set of entity changed (may be massaction)
     */
    public function executeList(array $ids)
    {
        //Works with a set of products (mass actions and so on)
        $allSuperLinks = $this->_getAllSuperLinks($ids);
        if ($allSuperLinks && is_array($allSuperLinks) && count($allSuperLinks)) {
            foreach ($allSuperLinks as $superLink) {
                try {
                    $this->prepareAndGenerateUrl($superLink['product_id'], $superLink['parent_id']);
                } catch (\Exception $e) {
                    $this->_logResults('Something went wrong while executing index! More Details :: '.$e->getMessage());
                }
            }
        }
    }


    /*
     * Works in runtime for a single entity using plugins
     */
    public function executeRow($id)
    {
        //Works in runtime for a single product using plugins
        $allSuperLinks = $this->_getAllSuperLinks($id);
        if ($allSuperLinks && is_array($allSuperLinks) && count($allSuperLinks)) {
            foreach ($allSuperLinks as $superLink) {
                try {
                    $this->prepareAndGenerateUrl($superLink['product_id'], $superLink['parent_id']);
                } catch (\Exception $e) {
                    $this->_logResults('Something went wrong while executing index! More Details :: '.$e->getMessage());
                }
            }
        }
    }

    /**
     * Load configurable product
     * @param int $filterId
     */
    protected function _getProduct($productId)
    {
        $product = $this->_productRepository->getById($productId);
        return ($product->getId()) ? $product : false;
    }

    /**
     * Get all/specific super links from catalog_product_super_link table.
     * @param int|array|null $filterId
     */
    protected function _getAllSuperLinks($filterId = null)
    {
        if ($this->_allSuperLinks == null) {
            $relation = $this->_configurableType->getRelationInfo();

            if ($relation && $relation->getTable() && $relation->getParentFieldName() && $relation->getChildFieldName()) {
                $select = $this->_connection->select()->from(
                    ['main' => $this->_resource->getTableName($relation->getTable())],
                    [$relation->getChildFieldName(),$relation->getParentFieldName()]
                );
                if (!is_null($filterId)) {
                    if (is_array($filterId)) {
                        $select->where(
                            'main.product_id IN (?)',
                            $filterId
                        );
                    } else {
                        $select->where(
                            'main.product_id = ?',
                            $filterId
                        );
                    }
                }
                $this->_allSuperLinks = $this->_connection->fetchAll($select);
            }
        }
        return $this->_allSuperLinks;
    }

    /**
     * Get all product attributes
     * @param int $parentId
     */
    protected function _getProductAttributes($parentId)
    {
        if (!array_key_exists($parentId, $this->_productAttributes)) {
            $parentProduct = $this->_getProduct($parentId);
            if ($parentProduct && $parentProduct->getTypeId() == ConfigurableType::TYPE_CODE) {
                $this->_productAttributes[$parentId] = $parentProduct->getTypeInstance()->getConfigurableAttributesAsArray($parentProduct);
            } else {
                throw new \Exception(sprintf(__("Product with id %u does not exists"), $parentId), 1);
            }
        }
        return $this->_productAttributes[$parentId];
    }

    /**
     * Get all attributes & prepare url suffix for rewrites
     * @param int $childId
     * @param int $parentId
     * @return void
     * @throws \Exception
     */
    public function prepareAndGenerateUrl($childId, $parentId)
    {
        $attributes = $this->_getProductAttributes($parentId);
        $childProduct = $this->_getProduct($childId);
        if ($childProduct) {
            $urlSuffix = '';
            foreach ($attributes as $attr) {
                $attrText = $childProduct->getAttributeText($attr['attribute_code']);
                foreach ($attr['values'] as $value) {
                    if ($value['label'] == $attrText) {
                        if (strpos($attrText, ' ') !== false) {
                            $attrText = str_replace(' ', '~', $attrText);
                        }
                        $urlSuffix .= '+'.$attr['attribute_code'].'-'.$attrText;
                    }
                }
            }
            /*$data = [
                UrlRewrite::ENTITY_ID => $childId,
                UrlRewrite::ENTITY_TYPE => Rewrite::ENTITY_TYPE_CUSTOM
            ];
            $rewrite = $this->_urlPersist->deleteByData($data);*/

            if ($urlSuffix) {
                try {
                    $this->_generateUrlRewrite($childId, $parentId, $urlSuffix);
                } catch (\Exception $e) {
                    $this->_logResults('Something went wrong while generating urls! More Details :: '.$e->getMessage());
                }
            }
        } else {
            throw new \Exception(sprintf(__("Product with id %u does not exists"), $childId), 1);
        }
    }
    
    /**
     * Generate Url rewrites based on all available urls of configurable product
     * @param int $childId
     * @param int $parentId
     * @param string $urlSuffix
     * @return void
     */
    protected function _generateUrlRewrite($childId, $parentId, $urlSuffix)
    {
        // Get all available urls of configurable product
        $data = [
            UrlRewrite::ENTITY_ID => $parentId,
            UrlRewrite::ENTITY_TYPE => Rewrite::ENTITY_TYPE_PRODUCT,
            UrlRewrite::REDIRECT_TYPE => 0,
        ];
        $availableRewrites = $this->_urlFinder->findAllByData($data);
        if ($availableRewrites) {
            foreach ($availableRewrites as $rewrite) {
                $storeId = $rewrite->getStoreId();
                $targetPath = $rewrite->getTargetPath();
                $requestPath = $rewrite->getRequestPath();
                $newRequestPath = '';
                $htmlText = strpos($requestPath, '.html') ? '.html' : '';
                if (strpos($requestPath, '/') === false) {
                    $urlPrefix = ($htmlText == '') ? $requestPath : substr($requestPath, 0, (strlen($requestPath)-5));
                    $newRequestPath = $urlPrefix.$urlSuffix.$htmlText;
                } else {
                    $pathArr = explode('/', $requestPath);
                    $pathWithoutCat = array_pop($pathArr);
                    $urlPrefix = ($htmlText == '') ? $pathWithoutCat : substr($pathWithoutCat, 0, (strlen($pathWithoutCat)-5));
                    $newRequestPath = $urlPrefix.$urlSuffix.$htmlText;
                    $newRequestPath = implode('/', $pathArr).'/'.$newRequestPath;
                }

                $urlRewrite = $this->_urlRewriteFactory->create();
                $urlRewrite->setEntityType(Rewrite::ENTITY_TYPE_CUSTOM);
                $urlRewrite->setEntityId($childId);
                $urlRewrite->setRequestPath($newRequestPath);
                $urlRewrite->setTargetPath($targetPath);
                $urlRewrite->setStoreId($storeId);
                $urlRewrite->setIsAutogenerated(1);
                $urlRewrite->setMetaData($rewrite->getMetaData());

                $oldRewrite = $this->_checkIfUrlExists($childId, $rewrite->getMetaData(), $storeId, $targetPath);
                if ($oldRewrite) {
                    if ((string)$oldRewrite->getRequestPath() !== (string)$newRequestPath) {
                        $urlRewritePrototype = $this->_urlRewriteService->create();
                        $urlRewritePrototype->setEntityType(Rewrite::ENTITY_TYPE_CUSTOM);
                        $urlRewritePrototype->setEntityId($childId);
                        $urlRewritePrototype->setRequestPath($newRequestPath);
                        $urlRewritePrototype->setTargetPath($targetPath);
                        $urlRewritePrototype->setStoreId($storeId);
                        $urlRewritePrototype->setIsAutogenerated(1);
                        $urlRewritePrototype->setMetaData($rewrite->getMetaData());
                        $this->_urlPersist->replace([$urlRewritePrototype]);
                    } else {
                        return true;
                    }
                } else {
                    $urlRewrite->save();
                }
            }
        }
    }

    /**
     * Check if url key exists by target path (after collection is saved)
     * @param AbstractModel $object
     * @param string $targetPath
     * @param int $storeId
     * @return string|bool
     */
    protected function _checkIfUrlExists($entityId, $metaData, $storeId, $targetPath)
    {
        $data = [
            UrlRewrite::ENTITY_ID => $entityId,
            UrlRewrite::ENTITY_TYPE => Rewrite::ENTITY_TYPE_CUSTOM,
            UrlRewrite::STORE_ID => $storeId,
            UrlRewrite::TARGET_PATH => $targetPath
        ];
        if ($metaData !== null && (is_array($metaData) && count($metaData) > 0)) {
            $data[UrlRewrite::METADATA] = $metaData;
        }
        
        $rewrite = $this->_urlFinder->findOneByData($data);
        return $rewrite;
    }

    /**
     * Remove child product url if parent product is deleted.
     * @param int $parentId
     * @param int $parentId
     */
    public function removeChildUrls($childId, $parentId)
    {
        // Get all available urls of configurable product
        $data = [
            UrlRewrite::ENTITY_ID => $parentId,
            UrlRewrite::ENTITY_TYPE => Rewrite::ENTITY_TYPE_PRODUCT,
            UrlRewrite::REDIRECT_TYPE => 0,
        ];
        
        $availableRewrites = $this->_urlFinder->findAllByData($data);
        
        if ($availableRewrites) {
            foreach ($availableRewrites as $rewrite) {
                $storeId = $rewrite->getStoreId();
                $targetPath = $rewrite->getTargetPath();
                $childData = [
                    UrlRewrite::ENTITY_ID => $childId,
                    UrlRewrite::ENTITY_TYPE => Rewrite::ENTITY_TYPE_CUSTOM,
                    UrlRewrite::STORE_ID => $storeId,
                    UrlRewrite::TARGET_PATH => $targetPath
                ];
                
                try {
                    $this->_urlPersist->deleteByData($childData);
                } catch (\Exception $e) {
                    $this->_logResults('Something went wrong while deleting child urls! More Details :: '.$e->getMessage());
                }
            }
        }
    }
}
