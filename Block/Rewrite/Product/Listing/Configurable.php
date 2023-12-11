<?php

namespace Lightweight\CpsdRefactored\Block\Rewrite\Product\Listing;

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

class Configurable extends \Magento\Swatches\Block\Product\Renderer\Listing\Configurable
{

    /**
     * @var OutputHelper
     */
    protected $outputHelper;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

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
     * @return string
     */
    protected function getRendererTemplate()
    {
        return 'Lightweight_CpsdRefactored::category/swatches/renderer.phtml';
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
     * Get FCPM helper
     * @return FcpmHelper
     */
    public function getCurrentCategory()
    {
        return $this->coreRegistry->registry('current_category');
    }

    /**
     * Encode data to json
     * @param array $data
     * @return json object
     */
    public function jsonEncode($data)
    {
        return $this->jsonEncoder->encode($data);
    }

    /**
     * Get all products data for swatch js
     * @return json object
     */
    public function getAllProductsData()
    {
        $allProducts = [];
        $cpsdHelper = $this->getCpsdHelper();
        $category = $this->getCurrentCategory();
        $isSwitchName = $cpsdHelper->isSwitchProductName($category);
        $isSwitchUrl = $cpsdHelper->isSwitchProductUrl($category);
        
        if (!$isSwitchName && !$isSwitchUrl) {
            return $this->jsonEncode($allProducts);
        }

        $product = $this->getProduct();

        $allProducts[0] = $this->_getProductData($product, $isSwitchName, $isSwitchUrl);

        $childProducts = $product->getTypeInstance()->getUsedProductIds($product);
        if (count($childProducts)) {
            foreach ($childProducts as $spd) {
                $childProduct = $this->productRepository->getById($spd);

                if (!$childProduct->isSalable()) {
                    continue;
                }

                $allProducts[$childProduct->getId()] = $this->_getProductData($childProduct, $isSwitchName, $isSwitchUrl);
            }
        }

        return $this->jsonEncode($allProducts);
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
     * @param boolean $isSwitchName
     * @param boolean $isSwitchUrl
     * @return array
     */
    protected function _getProductData($product, $isSwitchName = false, $isSwitchUrl = false)
    {
        $data = [];
        if ($isSwitchName) {
            $data['name'] = $this->outputHelper->productAttribute(
                $product,
                $product->getName(),
                'name'
            );
        }
        if ($isSwitchUrl) {
            $urlRewrite = $this->getUrlRewrite($product);
            $data['url'] = ($product->getVisibility() != 1) ? $product->getProductUrl() : '';
            $data['url_suffix'] = $urlRewrite;
        }
        return $data;
    }
}
