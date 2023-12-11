<?php

namespace Lightweight\CpsdRefactored\Block\Rewrite\Product;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product as CatalogProduct;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Store\Model\ScopeInterface;
use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Swatches\Helper\Media;
use Magento\Catalog\Helper\Output as OutputHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Lightweight\CpsdRefactored\Helper\Data as CpsdData;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;

class Configurable extends \Magento\Swatches\Block\Product\Renderer\Configurable
{

    /**
     * Path to template file with Swatch renderer.
     */
    const SWATCH_RENDERER_TEMPLATE = 'Lightweight_CpsdRefactored::product/view/configurable-swatch.phtml';

    /**
     * Path to default template file with standard Configurable renderer.
     */
    const CONFIGURABLE_RENDERER_TEMPLATE = 'Lightweight_CpsdRefactored::product/view/configurable.phtml';

    /**
     * @var OutputHelper
     */
    protected $outputHelper;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistryInterface;

    /**
     * @var CoreRegistry
     */
    protected $coreRegistry;

    /**
     * @var CpsdData
     */
    protected $cpsdData;

    /**
     * @var UrlFinderInterface
     */
    protected $_urlFinder;
    

    /**
     * @param Context $context
     * @param ArrayUtils $arrayUtils
     * @param EncoderInterface $jsonEncoder
     * @param Data $helper
     * @param CatalogProduct $catalogProduct
     * @param CurrentCustomer $currentCustomer
     * @param PriceCurrencyInterface $priceCurrency
     * @param ConfigurableAttributeData $configurableAttributeData
     * @param SwatchData $swatchHelper
     * @param Media $swatchMediaHelper
     * @param OutputHelper $outputHelper
     * @param ProductRepositoryInterface $productRepository
     * @param CpsdData $cpsdData
     * @param UrlFinderInterface $urlFinder
     * @param array $data other data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        EncoderInterface $jsonEncoder,
        Data $helper,
        CatalogProduct $catalogProduct,
        CurrentCustomer $currentCustomer,
        PriceCurrencyInterface $priceCurrency,
        ConfigurableAttributeData $configurableAttributeData,
        SwatchData $swatchHelper,
        Media $swatchMediaHelper,
        OutputHelper $outputHelper,
        ProductRepositoryInterface $productRepository,
        CpsdData $cpsdData,
        UrlFinderInterface $urlFinder,
        array $data = []
    ) {

        $this->outputHelper = $outputHelper;
        $this->productRepository = $productRepository;
        $this->stockRegistryInterface = $context->getStockRegistry();
        $this->coreRegistry = $context->getRegistry();
        $this->cpsdData = $cpsdData;
        $this->_urlFinder = $urlFinder;
        
        parent::__construct(
            $context,
            $arrayUtils,
            $jsonEncoder,
            $helper,
            $catalogProduct,
            $currentCustomer,
            $priceCurrency,
            $configurableAttributeData,
            $swatchHelper,
            $swatchMediaHelper,
            $data
        );
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    protected function getRendererTemplate()
    {
        return $this->isProductHasSwatchAttribute() ?
            self::SWATCH_RENDERER_TEMPLATE : self::CONFIGURABLE_RENDERER_TEMPLATE;
    }

    /**
     * Get FCPM helper
     * @return FcpmHelper
     */
    public function getCpsdHelper()
    {
        return $this->cpsdData;
    }

    /**
     * Get CPSD settings in json format
     * @return json object
     */
    public function getCpsdConfig()
    {
        $cpsdConfig = [];
        $cpsdHelper = $this->getCpsdHelper();
        if ($cpsdHelper->isEnabled($this->getProduct())) {
            $labelSelectors = $cpsdHelper->getAllSelectors();
            unset($labelSelectors['labels_to_update']);
            $cpsdConfig = [
                'enable' => $cpsdHelper->isEnabled($this->getProduct()),
                'labels' => explode(',', $cpsdHelper->getLabelsToUpdate()),
                'selectors' => $labelSelectors,
                'qty_inc_pos' => $cpsdHelper->getQtyIncPosition(),
                'preselect_type' => $cpsdHelper->getPreselectType($this->getProduct())
            ];
        }
        return $this->jsonEncoder->encode($cpsdConfig);
    }

    /**
     * Get all products data for swatch js
     * @return json object
     */
    public function getAllProductsData()
    {
        $allProducts = [];
        $childPrices = [];
        $product = $this->getProduct();
        $preselectType = $this->getCpsdHelper()->getPreselectType($product);
        $attributes = $product->getTypeInstance()->getUsedProductAttributes($product);
        $attrCode = [];
        foreach ($attributes as $key => $attr) {
            $attrCode[$key] = $attr->getAttributeCode();
        }

        $allProducts[0] = $this->_getProductData($product);

        // $childProducts = $product->getTypeInstance()->getUsedProducts($product);
        $childProducts = $product->getTypeInstance()->getUsedProductIds($product);
        if (count($childProducts)) {
            foreach ($childProducts as $spd) {
                $childProduct = $this->productRepository->getById($spd);

                if (!$childProduct->isSalable()) {
                    continue;
                }
                $spConfig = [];
                foreach ($attrCode as $key => $code) {
                    $attr = $product->getResource()->getAttribute($code);
                    $opId = $attr->getSource()->getOptionId($childProduct->getAttributeText($code));
                    $spConfig[$key] = $opId;
                }
                $childPrices[$childProduct->getId()] = $childProduct->getFinalPrice();

                $allProducts[$childProduct->getId()] = array_merge(['spConfig' => $spConfig], $this->_getProductData($childProduct));
            }
            $preselect = 0;
            if ($preselectType == 1) {
                if (count($childPrices)) {
                    $preselect = array_search(min($childPrices), $childPrices);
                }
            } elseif ($preselectType == 2) {
                if (count($childPrices)) {
                    $preselect = array_search(max($childPrices), $childPrices);
                }
            } elseif ($preselectType == 3) {
                $preselect = $this->getCpsdHelper()->getPreselectOption($product);
            }
            $allProducts['preselect'] = $preselect;
        }

        return $this->jsonEncoder->encode($allProducts);
    }

    /**
     * Get the url rewrite for product.
     * @param /Magento/Catalog/Model/Product $product
     * @return string
     */
    protected function getUrlRewrite($product)
    {
        $entityType = Rewrite::ENTITY_TYPE_CUSTOM;
        if ($product->getTypeId() == 'configurable') {
            $entityType = Rewrite::ENTITY_TYPE_PRODUCT;
        }
        $data = [
            UrlRewrite::ENTITY_ID => $product->getId(),
            UrlRewrite::ENTITY_TYPE => $entityType
        ];
        $allRewrites = $this->_urlFinder->findAllByData($data);
        if ($allRewrites) {
            foreach ($allRewrites as $rewrite) {
                if ((is_array($rewrite->getMetaData()) && count($rewrite->getMetaData()) == 0) || is_null($rewrite->getMetaData())) {
                    return $rewrite->getRequestPath();
                }
            }
        }
    }

    /**
     * Get product data
     * @param /Magento/Catalog/Model/Product $product
     * @return array
     */
    protected function _getProductData($product)
    {
        $labelsToUpdate = $this->getCpsdHelper()->getLabelsToUpdate();
        $data = [];
        if ($labelsToUpdate) {
            foreach (explode(',', $labelsToUpdate) as $label) {
                switch ($label) {
                    case 'history':
                        $urlRewrite = $this->getUrlRewrite($product);
                        $data['url'] = ($product->getVisibility() != 1) ? $product->getProductUrl() : '';
                        $data['url_suffix'] = $urlRewrite;
                        break;

                    case 'name':
                        $data['name'] = $this->outputHelper->productAttribute(
                            $product,
                            $product->getName(),
                            'name'
                        );
                        break;

                    case 'sku':
                        $data['sku'] = $product->getSku();
                        break;

                    case 'stock':
                        $stockItem = $this->stockRegistryInterface->getStockItem($product->getId());
                        $data['stock'] = [
                                            'qty'                   => $stockItem->getQty()*1,
                                            'is_in_stock'           => $stockItem->getIsInStock(),
                                            'enable_qty_increment'  => $stockItem->getQtyIncrements(),
                                            'qty_increment'         => $stockItem->getQtyIncrements(),
                                            'min_sale_qty'          => $stockItem->getMinSaleQty(),
                                            'max_sale_qty'          => $stockItem->getMaxSaleQty(),
                                            'backorders'            => $stockItem->getBackorders(),
                                            'manage_stock'          => $stockItem->getManageStock(),
                                            'instock_label'         => __('In Stock'),
                                            'outofstock_label'      => __('Out of Stock')
                                        ];
                        break;

                    case 'short_description':
                        $data['sdesc'] = $this->outputHelper->productAttribute(
                            $product,
                            $product->getShortDescription(),
                            'short_description'
                        );
                        break;

                    case 'description':
                        $desc = $this->outputHelper->productAttribute(
                            $product,
                            $product->getDescription(),
                            'description'
                        );
                        $data['desc'] = $desc ? $desc : __('N/A');
                        break;

                    case 'attributes':
                        $attrHtml = $this->getLayout()->createBlock('\Magento\Catalog\Block\Product\View\Attributes')->setProduct($product)->setTemplate('Magento_Catalog::product/view/attributes.phtml')->toHtml();
                        $data['attributes'] = $attrHtml ? $attrHtml : __('N/A');
                        break;

                    case 'meta_info':
                        $data['metainfo'] = [
                                                'title'     => $product->getMetaTitle() ? $product->getMetaTitle() : $product->getName(),
                                                'desc'      => $product->getMetaDescription() ? $product->getMetaDescription() : substr(strip_tags($product->getDescription()), 0, 255),
                                                'keyword'   => $product->getMetaKeyword()
                                            ];
                        break;
                }
            }
        }
        return $data;
    }
}
