<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Search\DataProvider\Product;

use Magento\Review\Model\ResourceModel\Review\Summary;

/**
 * Product rating summary provider
 */
class RatingProvider
{
    /**
     * @var int
     */
    private $reviewCode;

    /**
     * @var Summary
     */
    private $summaryResource;

    public function __construct(Summary $summaryResource)
    {
        $this->summaryResource = $summaryResource;
    }

    public function getProductRating(int $productId, int $storeId): ?int
    {
        $connection = $this->summaryResource->getConnection();
        $select = $connection->select();
        $select->from($this->summaryResource->getMainTable(), ['rating_summary']);
        $select->where('entity_pk_value = ?', $productId);
        $select->where('store_id = ?', $storeId);
        $select->where('entity_type = ?', $this->getProductReviewTypeId());

        $summary = $connection->fetchOne($select);

        return !empty($summary) ? (int) $summary : null;
    }

    private function getProductReviewTypeId(): int
    {
        if ($this->reviewCode === null) {
            $connection = $this->summaryResource->getConnection();
            $entitySelect = $connection->select();
            $entitySelect->from(
                ['review_entity' => $this->summaryResource->getTable('review_entity')],
                ['entity_id']
            );
            $entitySelect->where('entity_code = ?', 'product');

            $this->reviewCode = (int) $connection->fetchOne($entitySelect);
        }
        
        return $this->reviewCode;
    }
}
