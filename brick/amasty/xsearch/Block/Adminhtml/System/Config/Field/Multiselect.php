<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Block\Adminhtml\System\Config\Field;

class Multiselect extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->setData('size', min(count($element->getValues()), 10));
        return $element->getElementHtml();
    }
}
