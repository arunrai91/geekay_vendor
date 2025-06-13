<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface BlacklistSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get blacklist list
     *
     * @return \Amasty\Acart\Api\Data\BlacklistInterface[]
     */
    public function getItems();

    /**
     * Set blacklist list
     *
     * @param \Amasty\Acart\Api\Data\BlacklistInterface[] $items
     * @return \Amasty\Acart\Api\Data\BlacklistSearchResultsInterface
     */
    public function setItems(array $items);
}
