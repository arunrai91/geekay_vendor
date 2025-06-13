<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\Search;

class SearchResult
{
    /**
     * @var null|array[]
     */
    private $items = null;

    /**
     * @var null|int
     */
    private $resultsCount = null;

    public function getItems(): ?array
    {
        return $this->items;
    }

    public function setItems(?array $items): void
    {
        $this->items = $items;
    }

    public function getResultsCount(): ?int
    {
        return $this->resultsCount;
    }

    public function setResultsCount(?int $resultsCount): void
    {
        $this->resultsCount = $resultsCount;
    }
}
