<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Tokenizer extends Field
{
    protected function _getElementHtml(AbstractElement $element): string
    {
        if (count($element->getValues()) <= 1) {
            $element->setReadonly(true);
        }
        $element->setComment($this->getComment());

        return parent::_getElementHtml($element);
    }

    public function getComment(): string
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return $this->_escaper->escapeHtml(__(
            'The functionality is available as part of an active product subscription or support subscription.
            To upgrade and obtain functionality please follow the <a href="%1">link</a>.',
            'https://amasty.com/amcustomer/account/products/?utm_source=extension&utm_medium=backend&utm_campaign=upgrade_elastic'
        ), ['a']);
    }
}
