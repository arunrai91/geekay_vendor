<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Pro for Magento 2
 */

namespace Amasty\RulesPro\Model\Indexer;

use Amasty\RulesPro\Model\Indexer\PurchaseHistory\Action;
use Amasty\RulesPro\Model\Indexer\PurchaseHistory\IndexerHandlerFactory;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;

class PurchaseHistory implements IndexerActionInterface, MviewActionInterface
{
    public const INDEXER_ID = 'amasty_amrules_purchase_history_index';

    /**
     * @var IndexerHandlerFactory
     */
    private $indexerHandlerFactory;

    /**
     * @var Action
     */
    private $indexAction;

    /**
     * @var array
     */
    private $data;

    public function __construct(
        IndexerHandlerFactory $indexerHandlerFactory,
        Action $indexAction,
        array $data = ['indexer_id' => self::INDEXER_ID]
    ) {
        $this->indexerHandlerFactory = $indexerHandlerFactory;
        $this->indexAction = $indexAction;
        $this->data = $data;
    }

    public function execute($ids)
    {
        /** @var IndexerInterface $indexHandler */
        $indexHandler = $this->indexerHandlerFactory->create([
            'data' => $this->data
        ]);
        if (!count($ids)) {
            $indexHandler->cleanIndex([]);
            $indexHandler->saveIndex([], $this->indexAction->getIndexInsertIterator([]));
        } else {
            $customerIds = $this->indexAction->convertOrderIdsToCustomerIds($ids);
            if (!empty($customerIds)) {
                $indexHandler->deleteIndex([], new \ArrayIterator($customerIds));
                $indexHandler->saveIndex([], $this->indexAction->getIndexInsertIterator($customerIds));
            }
        }
    }

    public function executeFull()
    {
        $this->execute([]);
    }

    public function executeList(array $ids)
    {
        $this->execute($ids);
    }

    public function executeRow($id)
    {
        $this->execute([$id]);
    }
}
