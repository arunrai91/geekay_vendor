<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Sorting for Magento 2
 */

namespace Amasty\Sorting\Model\ResourceModel\Method;

use Amasty\Sorting\Model\ConfigProvider;
use Amasty\Sorting\Model\Elasticsearch\IsElasticSort;
use Amasty\Yotpo\Model\ResourceModel\YotpoReview;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;

class Toprated extends AbstractMethod
{
    public const MAIN_TABLE = 'review_entity_summary';

    /**
     * @var \Magento\Review\Model\ResourceModel\Review
     */
    protected $reviewResource;

    /**
     * @var int|null
     */
    private $entityTypeId = null;

    public function __construct(
        Context $context,
        \Magento\Framework\Escaper $escaper,
        ConfigProvider $configProvider,
        IsElasticSort $isElasticSort,
        \Magento\Review\Model\ResourceModel\Review $reviewResource,
        $connectionName = null,
        $methodCode = '',
        $methodName = ''
    ) {
        parent::__construct(
            $context,
            $escaper,
            $configProvider,
            $isElasticSort,
            $connectionName,
            $methodCode,
            $methodName
        );
        $this->reviewResource = $reviewResource;
    }

    /**
     * Returns Sorting method Table Column name
     * which is using for order collection
     *
     * @return string
     */
    public function getSortingColumnName()
    {
        return 'rating_summary_field';
    }

    /**
     * @return string
     */
    public function getSortingFieldName()
    {
        return 'rating_summary';
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->getSortingColumnName();
    }

    /**
     * {@inheritdoc}
     * This method is also used for @see Commented
     */
    public function apply($collection, $direction)
    {
        try {
            $collection->joinField(
                $this->getSortingColumnName(),          // alias
                $this->getIndexTableName(),         // table
                $this->getSortingFieldName(),   // field
                $this->getProductColumn() . '=entity_id',     // bind
                $this->getConditions(),          // conditions
                'left'                          // join type
            );
        } catch (LocalizedException $e) {
            // A joined field with this alias is already declared.
            $this->logger->warning(
                'Failed on join table for amasty sorting method: ' . $e->getMessage(),
                ['method_code' => $this->getMethodCode()]
            );
        } catch (\Exception $e) {
            $this->logger->critical($e, ['method_code' => $this->getMethodCode()]);
        }

        return $this;
    }

    /**
     * Get Review entity type id for product
     *
     * @return bool|int|null
     */
    private function getEntityTypeId()
    {
        if ($this->entityTypeId === null) {
            $this->entityTypeId = $this->reviewResource->getEntityIdByCode(
                \Magento\Review\Model\Review::ENTITY_PRODUCT_CODE
            );
        }

        return $this->entityTypeId;
    }

    public function getIndexTableName(): string
    {
        return $this->configProvider->isYotpoReviewsEnabled($this->getStoreId())
            ? YotpoReview::MAIN_TABLE
            : self::MAIN_TABLE;
    }

    private function getConditions(?int $storeId = null): array
    {
        $conditions = ['store_id' => $this->getStoreId($storeId)];
        if (!$this->configProvider->isYotpoReviewsEnabled($this->getStoreId())) {
            $conditions['entity_type'] = $this->getEntityTypeId();
        }

        return $conditions;
    }

    private function getProductColumn(): string
    {
        return $this->configProvider->isYotpoReviewsEnabled($this->getStoreId())
            ? 'product_id'
            : 'entity_pk_value';
    }

    /**
     * @inheritdoc
     */
    public function getIndexedValues(int $storeId, ?array $entityIds = [])
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable($this->getIndexTableName()),
            ['product_id' => $this->getProductColumn(), 'value' => $this->getSortingFieldName()]
        );
        foreach ($this->getConditions($storeId) as $field => $value) {
            $select->where($field . ' = ?', $value);
        }

        if (!empty($entityIds)) {
            $select->where($this->getProductColumn() . ' in(?)', $entityIds);
        }

        return $this->getConnection()->fetchPairs($select);
    }

    protected function getStoreId(?int $preferredStoreId = null): int
    {
        return $preferredStoreId ?? (int)$this->storeManager->getStore()->getId();
    }
}
