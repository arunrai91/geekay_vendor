<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface FilterSettingSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \Amasty\ShopbyBase\Api\Data\FilterSettingInterface[]
     */
    public function getItems();

    /**
     * @param \Amasty\ShopbyBase\Api\Data\FilterSettingInterface[] $items
     * @return void
     */
    public function setItems(array $items);
}
