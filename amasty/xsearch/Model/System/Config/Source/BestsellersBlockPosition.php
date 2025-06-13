<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\System\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class BestsellersBlockPosition implements OptionSourceInterface
{
    public const BEFORE_RECENTLY_VIEWED = 1;
    public const AFTER_RECENTLY_VIEWES = 2;

    public function toOptionArray()
    {
        return [
            [
                'label' => __('Show Before Recently Viewed'),
                'value' => self::BEFORE_RECENTLY_VIEWED
            ],
            [
                'label' => __('Show After Recently Viewed'),
                'value' => self::AFTER_RECENTLY_VIEWES
            ]
        ];
    }
}
