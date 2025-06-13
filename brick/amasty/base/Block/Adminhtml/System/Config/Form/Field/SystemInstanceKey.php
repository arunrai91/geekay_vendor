<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class SystemInstanceKey extends Field
{
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setDisabled(true);

        return parent::_getElementHtml($element) . $this->getCopyToClipboardBtnHtml($element);
    }

    private function getCopyToClipboardBtnHtml(AbstractElement $element): string
    {
        $isElementHasValue = !empty($element->getValue());
        $disabledAttribute = $isElementHasValue ? '' :'disabled';
        $elementSelector = '#' . $element->getHtmlId();
        $ariaLabel = __('Copy to clipboard')->render();

        return <<<BUTTON
            <div class="ambase-clip-btn-contaner">
                  <button
                type="button"
                class="ambase-clip-btn"
                aria-label="{$ariaLabel}"
                {$disabledAttribute}
                data-mage-init='{
                    "amBaseCopyToClipboardButton": {
                        "targetInputSelector": "{$elementSelector}"
                    }
                }'
            >
            </button>
        </div>
        BUTTON;
    }
}
