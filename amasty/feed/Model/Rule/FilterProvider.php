<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Rule;

use Amasty\Feed\Model\Rule\Filters\FilterInterface;

class FilterProvider
{
    /**
     * @var FilterInterface[]
     */
    private array $filters = [];

    /**
     * @param array $filterTypes ['filterName' => FilterClass ]
     */
    public function __construct(
        array $filterTypes = []
    ) {
        $this->initializeFilterTypes($filterTypes);
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    private function initializeFilterTypes(array $filterTypes): void
    {
        foreach ($filterTypes as $type => $filter) {
            if (!$filter instanceof FilterInterface) {
                throw new \LogicException(
                    sprintf('Filter type must implement %s', FilterInterface::class)
                );
            }
            $this->filters[$type] = $filter;
        }
    }
}
