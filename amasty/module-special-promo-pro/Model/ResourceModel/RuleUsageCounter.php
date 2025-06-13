<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Pro for Magento 2
 */

namespace Amasty\RulesPro\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Amasty\RulesPro\Api\RuleUsageRepositoryInterface;

class RuleUsageCounter extends AbstractDb
{
    public const TABLE_NAME = 'amasty_amrules_usage_counter';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'entity_id');
    }

    /**
     * @param int $salesRuleId
     *
     * @return int
     */
    public function getCountByRuleId(int $salesRuleId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable(self::TABLE_NAME), [RuleUsageRepositoryInterface::COUNT_USAGE_COLUMN])->where(
                'salesrule_id = ?',
                $salesRuleId
            );

        return (int)$connection->fetchOne($select);
    }

    /**
     * @return array
     */
    public function getCountByRules(): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable(self::TABLE_NAME),
            ['salesrule_id', RuleUsageRepositoryInterface::COUNT_USAGE_COLUMN]
        );

        return $connection->fetchAll($select);
    }

    /**
     * @param array $ruleIds
     */
    public function incrementUsageCountByRuleIds(array $ruleIds)
    {
        $where = ['salesrule_id IN (?)' => $ruleIds];
        $this->getConnection()->update(
            $this->getTable(self::TABLE_NAME),
            ['count' => new \Zend_Db_Expr('count+1')],
            $where
        );
    }

    /**
     * @param int $salesRuleId
     * @param int $count
     */
    public function saveUsageCount(int $salesRuleId, int $count = 0)
    {
        $connection = $this->getConnection();

        $rowCount = $connection->fetchOne(
            $connection->select()->from($this->getTable(self::TABLE_NAME), [new \Zend_Db_Expr('COUNT(*)')])->where(
                'salesrule_id = ?',
                $salesRuleId
            )
        );
        if ($rowCount > 0) {
            $connection->update(
                $this->getTable(self::TABLE_NAME),
                [RuleUsageRepositoryInterface::COUNT_USAGE_COLUMN => $count],
                ['salesrule_id = ?' => $salesRuleId]
            );
        } else {
            $connection->insert(
                $this->getTable(self::TABLE_NAME),
                ['salesrule_id' => $salesRuleId, RuleUsageRepositoryInterface::COUNT_USAGE_COLUMN => $count]
            );
        }
    }
}
