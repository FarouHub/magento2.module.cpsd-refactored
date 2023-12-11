<?php

namespace Lightweight\CpsdRefactored\Plugin\Pricing\Render;

use Psr\Log\LoggerInterface;
use Lightweight\CpsdRefactored\Helper\Data as CPSDHelper;
use Magento\Framework\Registry;

class TierPriceBox
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
     * @var Registry
     */
    protected $_registry;
 
    /**
     * @param LoggerInterface $loggerInterface
     * @param CPSDHelper $cpsdHelper
     * @param Registry $registry
     */
    public function __construct(
        LoggerInterface $loggerInterface,
        CPSDHelper $cpsdHelper,
        Registry $registry
    ) {
        $this->_logger = $loggerInterface;
        $this->_cpsdHelper = $cpsdHelper;
        $this->_registry = $registry;
    }

    public function beforeSetTemplate($subject, $template)
    {
        $currProduct = $this->_registry->registry('current_product');
        if ($currProduct && $currProduct->getTypeId() != 'configurable') {
            return [$template];
        }

        $cpsdHelper = $this->_cpsdHelper;
        if ($cpsdHelper->isCptpEnable($currProduct) && $cpsdHelper->isEnableGridLook() && !$subject->getData('is_fcpm')) {
            if ($cpsdHelper->isEnableQtyAutofill()) {
                $subject->setAutofill(true);
            }
            if (($gridTitle = $cpsdHelper->getGridTitle()) != '') {
                $subject->setGridTitle($gridTitle);
            }
            if (version_compare($cpsdHelper->getMagentoVersion(), '2.1.11', '>=')) {
                $template = 'Lightweight_CpsdRefactored::product/price/tier_price.phtml';
            } else {
                $template = 'Lightweight_CpsdRefactored::product/price/tier_price_2.1.x.phtml';
            }

            $this->_logger->info('Best4Mage_CPSD::Tier prices template might have changed if configuration is enabled.');
        }
        return [$template];
    }
}
