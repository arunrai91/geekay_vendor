<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Block\Adminhtml\System\Config\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;

class TestEmail extends Field
{
    protected function _getElementHtml(AbstractElement $element): string
    {
        $element->setData('placeholder', 'test@amasty.com');

        return parent::_getElementHtml($element);
    }
}
