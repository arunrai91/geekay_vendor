<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Rule\Filters;

use Amasty\Feed\Model\Feed;
use Amasty\Feed\Model\Rule\Condition\Sql\Builder;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

class ConditionFilter implements FilterInterface
{
    /**
     * @var Builder
     */
    private Builder $sqlBuilder;

    public function __construct(
        Builder $sqlBuilder
    ) {
        $this->sqlBuilder = $sqlBuilder;
    }

    public function apply(Collection $productCollection, Feed $model): void
    {
        $conditions = $model->getRule()->getConditions();
        $conditions->collectValidatedAttributes($productCollection);
        $this->sqlBuilder->attachConditionToCollection($productCollection, $conditions);
    }
}
