<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Indexer\Feed;

use Amasty\Feed\Model\Indexer\AbstractIndexer;
use Magento\Framework\App\Cache\Type\Block;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;

class FeedRuleIndexer extends AbstractIndexer
{
    /**
     * Override constructor. Indexer is changed
     */
    public function __construct(
        IndexBuilder $indexBuilder,
        ManagerInterface $eventManager
    ) {
        parent::__construct($indexBuilder, $eventManager);
        $this->indexBuilder = $indexBuilder;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecuteList($ids)
    {
        try {
            $this->indexBuilder->reindexByFeedIds(array_unique($ids));
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecuteRow($id)
    {
        try {
            $this->indexBuilder->reindexByFeedId($id);
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        return [
            Block::CACHE_TAG
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function executeFull()
    {
        try {
            $this->indexBuilder->reindexFull();
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }
}
