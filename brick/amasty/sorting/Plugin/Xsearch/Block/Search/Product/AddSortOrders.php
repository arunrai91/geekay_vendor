<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Sorting for Magento 2
 */

namespace Amasty\Sorting\Plugin\Xsearch\Block\Search\Product;

use Amasty\Sorting\Model\ConfigProvider;
use Amasty\Sorting\Model\Method\GetDefaultSortOrders;
use Amasty\Xsearch\Block\Search\Product as XsearchProductBlock;

class AddSortOrders
{
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
    public function afterGetSortOrders(XsearchProductBlock $subject, array $sortOrders): array
    {
        if ($this->configProvider->isUsedInSearchPopup()) {
            $sortOrders = $this->getDefaultSortOrders->execute(GetDefaultSortOrders::SEARCH_PAGE);
        }

        return $sortOrders;
    }
}
