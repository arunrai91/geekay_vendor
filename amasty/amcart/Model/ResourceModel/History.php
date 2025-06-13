<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Model\ResourceModel;

use Amasty\Acart\Model\History as HistoryModel;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

class History extends AbstractDb
{
    public const TABLE_NAME = 'amasty_acart_history';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, HistoryModel::HISTORY_ID);
    }

    public function setInProgress(array $ids)
    {
        $this->massStatusUpdate(HistoryModel::STATUS_IN_PROGRESS, $ids);
    }

    public function setFailed(array $ids)
    {
        $this->massStatusUpdate(HistoryModel::STATUS_FAILED, $ids);
    }

    /**
     * @param int[] $ids
     * @return void
     */
    public function massDelete(array $ids): void
    {
        $connection = $this->getConnection();
        $connection->delete(
            $this->getMainTable(),
            $connection->quoteInto(HistoryModel::HISTORY_ID . ' IN(?)', $ids)
        );
    }

    private function massStatusUpdate(string $status, array $ids)
    {
        $connection = $this->getConnection();
        $connection->update(
            $this->getMainTable(),
            [HistoryModel::STATUS => $status],
            $connection->quoteInto(HistoryModel::HISTORY_ID . ' IN(?)', $ids)
        );
    }
}
