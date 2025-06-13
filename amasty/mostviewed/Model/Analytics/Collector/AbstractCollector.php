<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Analytics\Collector;

use Amasty\Mostviewed\Api\AnalyticRepositoryInterface;
use Amasty\Mostviewed\Model\Analytics\Analytic;
use Amasty\Mostviewed\Model\Analytics\Collector\Utils\GetActionSelect;
use Amasty\Mostviewed\Model\Analytics\Collector\Utils\GetAnalyticsItems;
use Amasty\Mostviewed\Model\Analytics\Collector\Utils\GetGroupIds;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;

class AbstractCollector
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AnalyticRepositoryInterface
     */
    private $analyticRepository;

    /**
     * @var GetActionSelect
     */
    private $getActionSelect;

    /**
     * @var GetGroupIds
     */
    private $getGroupIds;

    /**
     * @var GetAnalyticsItems
     */
    private $getAnalyticsItems;

    /**
     * @var AdapterInterface|null
     */
    private $connection = null;

    public function __construct(
        AnalyticRepositoryInterface $analyticRepository,
        ResourceConnection $resourceConnection,
        GetActionSelect $getActionSelect,
        GetGroupIds $getGroupIds,
        GetAnalyticsItems $getAnalyticsItems
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->analyticRepository = $analyticRepository;
        $this->getActionSelect = $getActionSelect;
        $this->getGroupIds = $getGroupIds;
        $this->getAnalyticsItems = $getAnalyticsItems;
    }

    protected function updateAnalytics(string $actionType, string $analyticsType, ?int $clickType = null)
    {
        $connection = $this->getConnection();
        $actionSelect = $this->getActionSelect->execute($actionType);
        $analyticsItems = $this->getAnalyticsItems->execute($analyticsType);

        foreach ($this->getGroupIds->execute() as $groupId) {
            /** @var Analytic $item */
            $items = $analyticsItems[$groupId] ?? $this->analyticRepository->getNew();

            foreach ($items as $date => $item) {
                $condition = $date ? $connection->quoteInto('date = ?', $item->getDate()) : 'ISNULL(date)';
                $actionSelect
                    ->where('id > ?', $item->getVersionId())
                    ->having('block_id = ?', $groupId)
                    ->having($condition);

                if ($clickType !== null) {
                    $actionSelect->where('click_type =?', $clickType);
                }

                if ($statistics = $connection->fetchRow($actionSelect)) {
                    $item
                        ->setBlockId($groupId)
                        ->setCounter($item->getCounter() + $statistics['counter'])
                        ->setType($analyticsType)
                        ->setVersionId($statistics['version_id']);

                    if ($statistics['date']) {
                        $item->setDate($statistics['date']);
                    }
                    $this->analyticRepository->save($item);
                }
                $actionSelect->reset(Select::WHERE);
                $actionSelect->reset(Select::HAVING);
            }
        }
    }

    protected function addNewStatistics(string $actionType, string $analyticsType, ?int $clickType = null): void
    {
        $connection = $this->getConnection();

        $analyticsSelect = $connection->select()
            ->from(
                $this->resourceConnection->getTableName('mostviewed_analytics'),
                ['date']
            )
            ->where('type = ?', $analyticsType)
            ->where('date IS NOT NULL');

        $actionSelect = $this->getActionSelect->execute($actionType)
            ->columns(['block_id'])
            ->having('date IS NOT NULL');

        if ($analyticsItemsForExclude = $connection->fetchCol($analyticsSelect)) {
            $actionSelect->where('date NOT IN(?)', $analyticsItemsForExclude);
        }

        if ($clickType !== null) {
            $actionSelect->where('click_type = ?', $clickType);
        }

        foreach ($connection->fetchAll($actionSelect) as $statistics) {
            $item = $this->analyticRepository->getNew();
            $item->setBlockId($statistics['block_id'])
                ->setCounter($statistics['counter'])
                ->setType($analyticsType)
                ->setVersionId($statistics['version_id'])
                ->setDate($statistics['date']);
            $this->analyticRepository->save($item);
        }
    }

    private function getConnection(): AdapterInterface
    {
        if ($this->connection === null) {
            $this->connection = $this->resourceConnection->getConnection();
        }

        return $this->connection;
    }
}
