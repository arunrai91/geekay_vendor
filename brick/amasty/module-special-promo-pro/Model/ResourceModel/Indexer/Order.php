<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Pro for Magento 2
 */

namespace Amasty\RulesPro\Model\ResourceModel\Indexer;

use Magento\Sales\Model\ResourceModel\Order as OrderResource;

class Order
{
    /**
     * @var OrderResource
     */
    private $salesOrderResource;

    public function __construct(
        OrderResource $salesOrderResource
    ) {
        $this->salesOrderResource = $salesOrderResource;
    }

    /**
     * @param array $ids order ids
     *
     * @return array unique customer ids
     */
    public function retrieveCustomerIdsByOrderIds(array $ids): array
    {
        $connection = $this->salesOrderResource->getConnection();
        $select = $connection->select()->from(
            ['o' => $this->salesOrderResource->getMainTable()],
            ['customer_id']
        )->joinInner(
            ['customer' => $this->salesOrderResource->getTable('customer_entity')],
            'customer.entity_id = o.customer_id',
            []
        )->where(
            'o.' . $this->salesOrderResource->getIdFieldName() . ' IN (?)',
            $ids
        )->where(
            'o.customer_id IS NOT NULL'
        )->group(
            'o.customer_id'
        );

        return $connection->fetchCol($select);
    }

    /**
     * Retrieve order data such as orders count & orders base sum for customers
     *
     * @param array $ids customer ids
     *
     * @return array data with customer_id, order count and order sum
     */
    public function retrieveIndexData(array $ids): array
    {
        $connection = $this->salesOrderResource->getConnection();

        if (empty($ids)) {
            $customersCondition = ['o.customer_id IS NOT NULL'];
        } else {
            $customersCondition = ['o.customer_id IN (?)', $ids];
        }

        $select = $connection->select()
            ->from(
                ['o' => $this->salesOrderResource->getMainTable()],
                ['customer_id', new \Zend_Db_Expr('COUNT(*) as c'), new \Zend_Db_Expr('SUM(o.base_grand_total) as s')]
            )->joinInner(
                ['customer' => $this->salesOrderResource->getTable('customer_entity')],
                'customer.entity_id = o.customer_id',
                []
            )->where(
                ...$customersCondition
            )->where(
                'o.state = ?',
                \Magento\Sales\Model\Order::STATE_COMPLETE
            )->group(
                'o.customer_id'
            );

        return (array)$connection->fetchAll($select);
    }
}
