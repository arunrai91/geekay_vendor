<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Indexer;

use Amasty\Feed\Exceptions\LockProcessException;
use Amasty\Feed\Model\Indexer\Feed\FeedRuleProcessor;
use Amasty\Feed\Model\Indexer\Product\ProductFeedProcessor;
use Magento\Framework\Indexer\StateInterface;
use Magento\Indexer\Model\Indexer;
use Psr\Log\LoggerInterface;

class LockManager
{
    /**
     * @var array
     */
    private $indexers = [
        ProductFeedProcessor::INDEXER_ID,
        FeedRuleProcessor::INDEXER_ID
    ];

    /**
     * @var Indexer
     */
    private $indexer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Indexer $indexer,
        LoggerInterface $logger
    ) {
        $this->indexer = $indexer;
        $this->logger = $logger;
    }

    /**
     * @throws LockProcessException
     */
    public function validateLock(): void
    {
        if ($this->isIndexWorking()) {
            throw new LockProcessException();
        }
    }

    private function isIndexWorking(): bool
    {
        $isWorking = false;
        try {
            foreach ($this->indexers as $indexer) {
                $this->indexer->load($indexer);
                if ($this->indexer->getStatus() === StateInterface::STATUS_WORKING) {
                    $isWorking = true;
                    break;
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e);
        }

        return $isWorking;
    }
}
