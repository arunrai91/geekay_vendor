<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Page for Magento 2 (System)
 */

namespace Amasty\ShopbyPage\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface PageSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \Amasty\ShopbyPage\Api\Data\PageInterface[]
     */
    public function getItems();

    /**
     * @param \Amasty\ShopbyPage\Api\Data\PageInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
