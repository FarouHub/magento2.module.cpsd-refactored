<?php

namespace Lightweight\CpsdRefactored\Block\Config;

class Support extends \Magento\Config\Block\System\Config\Form\Field
{

    protected function _renderValue(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {

        $html = '<td class="value">';
        $html .= '<a target="_blank" href="http://support.best4mage.com" title="Best4Mage Support">http://support.best4mage.com</a>';
        $html .= '</td>';
        return $html;
    }
}
