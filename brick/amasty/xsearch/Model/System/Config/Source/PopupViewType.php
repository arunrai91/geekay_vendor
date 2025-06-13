<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\System\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PopupViewType implements OptionSourceInterface
{
    public const GRID_VIEW = 0;
    public const LIST_VIEW = 1;

    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::GRID_VIEW,
                'label' => __('Grid View')
            ],
            [
                'value' => self::LIST_VIEW,
                'label' => __('List View')
            ]
        ];
    }
}
