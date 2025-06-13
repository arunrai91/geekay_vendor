<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Sorting for Magento 2
 */

namespace Amasty\Sorting\Model\ResourceModel\Method;

class Commented extends Toprated
{
    /**
     * Returns Sorting method Table Column name
     * which is using for order collection
     *
     * @return string
     */
    public function getSortingColumnName()
    {
        return 'reviews_count';
    }

    public function getSortingFieldName(): string
    {
        return $this->configProvider->isYotpoReviewsEnabled($this->getStoreId())
            ? 'total_reviews'
            : 'reviews_count';
    }
}
