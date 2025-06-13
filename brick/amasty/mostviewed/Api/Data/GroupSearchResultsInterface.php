<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface GroupSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface[]
     */
    public function getItems();

    /**
     * @param \Amasty\Mostviewed\Api\Data\GroupInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
