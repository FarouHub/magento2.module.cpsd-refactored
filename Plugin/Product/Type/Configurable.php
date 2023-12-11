<?php

namespace Lightweight\CpsdRefactored\Plugin\Product\Type;

use Psr\Log\LoggerInterface;
use Lightweight\CpsdRefactored\Helper\Data as CPSDHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Locale\Format;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;

class Configurable
{
   
    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var CPSDHelper
     */
    protected $_cpsdHelper;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;
    
    /**
     * @var Format
     */
    private $localeFormat;

    /**
     * @var DecoderInterface
     */
    protected $_jsonDecoder;

    /**
     * @var EncoderInterface
     */
    protected $_jsonEncoder;
 
    /**
     * @param LoggerInterface $loggerInterface
     * @param CPSDHelper $cpsdHelper
     * @param ProductRepositoryInterface $productRepository
     * @param Format $localeFormat
     * @param DecoderInterface $jsonDecoder
     * @param EncoderInterface $jsonEncoder
     */
    public function __construct(
        LoggerInterface $loggerInterface,
        CPSDHelper $cpsdHelper,
        ProductRepositoryInterface $productRepository,
        Format $localeFormat,
        DecoderInterface $jsonDecoder,
        EncoderInterface $jsonEncoder
    ) {
        $this->_logger = $loggerInterface;
        $this->_cpsdHelper = $cpsdHelper;
        $this->_productRepository = $productRepository;
        $this->localeFormat = $localeFormat;
        $this->_jsonDecoder = $jsonDecoder;
        $this->_jsonEncoder = $jsonEncoder;
    }

    public function afterGetJsonConfig(
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject,
        $result
    ) {
        if ($this->_cpsdHelper->isCptpEnable($subject->getProduct())) {
            $jsonConfig = $this->_jsonDecoder->decode($result);
            $jsonConfig['optionPrices'] = $this->_getOptionPrices($subject);
            $result = $this->_jsonEncoder->encode($jsonConfig);
            $this->_logger->info('Best4Mage_CPSD::Tier prices might have changed in json config if configuration is enabled.');
        }
        return $result;
    }

    /**
     * Recalculate the optionPrices to adjust the Tier prices according to CPSD settings.
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     * @return array
     */
    protected function _getOptionPrices($subject)
    {
        $cpsdHelper = $this->_cpsdHelper;
        $isUseForAll = $cpsdHelper->isUseForAll($subject->getProduct());
        $commonTierPrice = [];
        if ($isUseForAll) {
            $tierProductId = $subject->getProduct()->getCpsdTpTierProduct();
            if ($tierProductId != '') {
                $tierProduct = $this->_productRepository->getById($tierProductId);
                $priceInfo = $tierProduct->getPriceInfo();
                $tierPriceModel =  $priceInfo->getPrice('tier_price');
                $tierPricesList = $tierPriceModel->getTierPriceList();
                foreach ($tierPricesList as $tierPrice) {
                    $commonTierPrice[] = [
                        'qty' => $this->localeFormat->getNumber($tierPrice['price_qty']),
                        'price' => $this->localeFormat->getNumber($tierPrice['price']->getValue()),
                        'percentage' => $this->localeFormat->getNumber(
                            $tierPriceModel->getSavePercent($tierPrice['price'])
                        ),
                    ];
                }
            }
        }

        $prices = [];
        
        foreach ($subject->getAllowProducts() as $product) {
            $tierPrices = [];
            $priceInfo = $product->getPriceInfo();
            $tierPriceModel =  $priceInfo->getPrice('tier_price');
            $tierPricesList = $tierPriceModel->getTierPriceList();
            foreach ($tierPricesList as $tierPrice) {
                $tierPrices[] = [
                    'qty' => $this->localeFormat->getNumber($tierPrice['price_qty']),
                    'price' => $this->localeFormat->getNumber($tierPrice['price']->getValue()),
                    'percentage' => $this->localeFormat->getNumber(
                        $tierPriceModel->getSavePercent($tierPrice['price'])
                    ),
                ];
            }

            $prices[$product->getId()] =
                [
                    'oldPrice' => [
                        'amount' => $this->localeFormat->getNumber(
                            $priceInfo->getPrice('regular_price')->getAmount()->getValue()
                        ),
                    ],
                    'basePrice' => [
                        'amount' => $this->localeFormat->getNumber(
                            $priceInfo->getPrice('final_price')->getAmount()->getBaseAmount()
                        ),
                    ],
                    'finalPrice' => [
                        'amount' => $this->localeFormat->getNumber(
                            $priceInfo->getPrice('final_price')->getAmount()->getValue()
                        ),
                    ],
                    'tierPrices' => ($isUseForAll) ? $commonTierPrice : $tierPrices,
                 ];
        }
        return $prices;
    }
}
