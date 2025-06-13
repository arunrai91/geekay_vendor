<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Page for Magento 2 (System)
 */

namespace Amasty\ShopbyPage\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Robots implements OptionSourceInterface
{
    public const NO_SELECT = 0;
    public const ROBOTS_INDEX_FOLLOW = 'INDEX,FOLLOW';
    public const ROBOTS_NOINDEX_FOLLOW = 'NOINDEX,FOLLOW';
    public const ROBOTS_INDEX_NOFOLLOW = 'INDEX,NOFOLLOW';
    public const ROBOTS_NOINDEX_NOFOLLOW = 'NOINDEX,NOFOLLOW';

    public function toOptionArray(): array
    {
        $optionArray = [];
        $arr = $this->toArray();
        foreach ($arr as $value => $label) {
            $optionArray[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $optionArray;
    }

    public function toArray(): array
    {
        return [
            self::NO_SELECT => __('--Please Select--'),
            self::ROBOTS_INDEX_FOLLOW => __('INDEX, FOLLOW'),
            self::ROBOTS_NOINDEX_FOLLOW => __('NOINDEX, FOLLOW'),
            self::ROBOTS_INDEX_NOFOLLOW => __('INDEX, NOFOLLOW'),
            self::ROBOTS_NOINDEX_NOFOLLOW => __('NOINDEX, NOFOLLOW')
        ];
    }
}
