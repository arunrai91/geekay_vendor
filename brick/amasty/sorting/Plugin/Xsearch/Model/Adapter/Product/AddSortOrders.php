<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Sorting for Magento 2
 */

namespace Amasty\Sorting\Plugin\Xsearch\Model\Adapter\Product;

use Amasty\Sorting\Model\ConfigProvider;
use Amasty\Sorting\Model\Method\GetDefaultSortOrders;
use Amasty\Xsearch\Model\Adapter\Product as XSearchProductAdapter;

class AddSortOrders
{
    private const PRICE_SORT_ORDERS = [
        'price_desc',
        'price_asc'
    ];

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var GetDefaultSortOrders
     */
    private $getDefaultSortOrders;

    public function __construct(ConfigProvider $configProvider, GetDefaultSortOrders $getDefaultSortOrders)
    {
        $this->configProvider = $configProvider;
        $this->getDefaultSortOrders = $getDefaultSortOrders;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSortOrders(XSearchProductAdapter $subject, array $sortOrders): array
    {
        if ($this->configProvider->isUsedInSearchPopup()) {
            $sortOrders = $this->getDefaultSortOrders->execute(GetDefaultSortOrders::SEARCH_PAGE);

            $sortOrders = $this->updatePriceOrders($sortOrders);
        }

        return $sortOrders;
    }

    private function updatePriceOrders(array $sortOrders): array
    {
        $result = [];

        foreach ($sortOrders as $order => $direction) {
            if (in_array($order, self::PRICE_SORT_ORDERS, true)) {
                $result['price'] = $direction;
            } else {
                $result[$order] = $direction;
            }
        }

        return $result;
    }
}
