<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\ResourceModel\RelevanceRule;

use Amasty\ElasticSearch\Api\Data\RelevanceRuleInterface;
use Amasty\ElasticSearch\Model\RelevanceRule;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Grid extends SearchResult
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->document = RelevanceRule::class;
    }

    //phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = RelevanceRuleInterface::TABLE_NAME,
        $resourceModel = \Amasty\ElasticSearch\Model\ResourceModel\RelevanceRule::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }
}
