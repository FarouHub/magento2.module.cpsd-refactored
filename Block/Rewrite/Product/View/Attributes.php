<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Lightweight\CpsdRefactored\Block\Rewrite\Product\View;

class Attributes extends \Magento\Catalog\Block\Product\View\Attributes
{
    /**
     * @var Product
     */
    protected $_product = null;

    public function getProduct()
    {
        if ($this->getData('product')) {
            return $this->getData('product');
        }
        if (!$this->_product) {
            $this->_product = $this->_coreRegistry->registry('product');
        }
        return $this->_product;
    }
}
