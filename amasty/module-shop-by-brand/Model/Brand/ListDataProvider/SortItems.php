<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Model\Brand\ListDataProvider;

use Amasty\ShopbyBrand\Model\Brand\BrandDataInterface;
use Amasty\ShopbyBrand\Model\Source\SliderSort;

class SortItems
{
    /**
     * @param BrandDataInterface[] $items
     * @param string $sortBy
     *
     * @return BrandDataInterface[]
     */
    public function execute(array $items, string $sortBy): array
    {
        switch ($sortBy) {
            case SliderSort::NAME:
                usort($items, [$this, 'sortByName']);
                break;
            case SliderSort::POSITION:
                usort($items, [$this, 'sortByPosition']);
                break;
        }

        return $items;
    }

    /**
     * @param BrandDataInterface $itemA
     * @param BrandDataInterface $b
     *
     * @return int
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod) used in usort
     */
    private function sortByName(BrandDataInterface $itemA, BrandDataInterface $itemB): int
    {
        return strncmp($itemA->getLabel(), $itemB->getLabel(), 10);
    }

    /**
     * @param BrandDataInterface $itemA
     * @param BrandDataInterface $itemB
     *
     * @return int
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod) used in usort
     */
    private function sortByPosition(BrandDataInterface $itemA, BrandDataInterface $itemB): int
    {
        return $itemA->getPosition() - $itemB->getPosition();
    }
}
