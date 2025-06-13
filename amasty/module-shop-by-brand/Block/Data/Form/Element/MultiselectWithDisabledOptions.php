<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Block\Data\Form\Element;

class MultiselectWithDisabledOptions extends \Magento\Framework\Data\Form\Element\Multiselect
{
    protected function _optionToHtml($option, $selected)
    {
        $optionHtml = parent::_optionToHtml($option, $selected);
        if (!empty($option['disabled'])) {
            $optionHtml = preg_replace('/<([^>]*)>/', '<$1 disabled>', $optionHtml, 1);
        }

        return $optionHtml;
    }
}
