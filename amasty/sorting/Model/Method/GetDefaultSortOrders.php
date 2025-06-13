<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Sorting for Magento 2
 */

namespace Amasty\Sorting\Model\Method;

use Amasty\Sorting\Model\Catalog\Toolbar\GetDefaultDirection;
use Amasty\Sorting\Model\ConfigProvider;
use Amasty\Sorting\Model\Source\Image as ImageSource;
use Amasty\Sorting\Model\Source\Stock as StockSource;
use Magento\Framework\DB\Select;

class GetDefaultSortOrders
{
    public const CATEGORY_PAGE = 'category';
    public const SEARCH_PAGE = 'search';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var IsAvailableForSorting
     */
    private $isAvailableForSorting;

    /**
     * @var GetDefaultDirection
     */
    private $getDefaultDirection;

    /**
     * @var bool
     */
    private $withHighOrders;

    public function __construct(
        ConfigProvider $configProvider,
        IsAvailableForSorting $isAvailableForSorting,
        GetDefaultDirection $getDefaultDirection,
        bool $withHighOrders = false
    ) {
        $this->configProvider = $configProvider;
        $this->isAvailableForSorting = $isAvailableForSorting;
        $this->getDefaultDirection = $getDefaultDirection;
        $this->withHighOrders = $withHighOrders;
    }

    /**
     * Return available default sort orders (first,second,third).
     * High orders must be included if constructor parameter $withHighOrders set true.
     *
     * @param string $pageType category|search
     * @param int|null $storeId
     * @return array ['attributeCode' => 'direction', ...]
     */
    public function execute(string $pageType, int $storeId = null): array
    {
        $sortOrders = [];

        if ($this->withHighOrders) {
            $sortOrders += $this->getHighSortOrders($pageType, $storeId);
        }

        foreach ($this->getDefaultSortOrders($pageType, $storeId) as $sortOrder) {
            if ($this->isAvailableForSorting->execute($sortOrder)) {
                $sortOrders[$sortOrder] = $this->getDefaultDirection->execute($sortOrder);
            }
        }

        return $sortOrders;
    }

    /**
     * @return array ['attributeCode' => 'direction', ...]
     */
    private function getHighSortOrders(string $pageType, ?int $storeId): array
    {
        $highSortOrders = [];

        $isApplyNonImagesLast = $this->configProvider->isNonImagesLastEnabled($storeId);
        if ($isApplyNonImagesLast === ImageSource::SHOW_LAST
            || ($isApplyNonImagesLast === ImageSource::SHOW_LAST_FOR_CATALOG && $pageType === self::CATEGORY_PAGE)
        ) {
            $highSortOrders['non_images_last'] = Select::SQL_DESC;
        }

        $isApplyOutOfStockLast = $this->configProvider->isNonImagesLastEnabled($storeId);
        if ($isApplyOutOfStockLast === StockSource::SHOW_LAST
            || ($isApplyOutOfStockLast === StockSource::SHOW_LAST_FOR_CATALOG && $pageType === self::CATEGORY_PAGE)
        ) {
            $highSortOrders['out_of_stock_last'] = Select::SQL_DESC;
        }

        $globalAttributeCode = $this->configProvider->getGlobalSorting();
        if ($globalAttributeCode && $this->isAvailableForSorting->execute($globalAttributeCode)) {
            $highSortOrders[$globalAttributeCode] = $this->configProvider->getGlobalSortingDirection();
        }

        return $highSortOrders;
    }

    /**
     * @return string[]
     */
    private function getDefaultSortOrders(string $pageType, ?int $storeId): array
    {
        switch ($pageType) {
            case self::CATEGORY_PAGE:
                return $this->configProvider->getDefaultSortingCategoryPages($storeId);
            case self::SEARCH_PAGE:
                return $this->configProvider->getDefaultSortingSearchPages($storeId);
            default:
                return [];
        }
    }
}
