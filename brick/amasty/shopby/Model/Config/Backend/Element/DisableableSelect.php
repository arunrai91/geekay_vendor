<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Config\Backend\Element;

use Magento\Framework\Data\Form\Element\Select;

/**
 * Select with disableable options to show promo items
 */
class DisableableSelect extends Select
{
    /**
     * Format an option as Html
     *
     * @param array $option
     * @param array $selected
     * @return string
     */
    protected function _optionToHtml($option, $selected)
    {
        $html = parent::_optionToHtml($option, $selected);

        if ($option['disabled'] ?? false) {
            $html = preg_replace('/<option /i', '<option disabled ', $html);
        }

        return $html;
    }
}
