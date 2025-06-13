<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Sorting for Magento 2
 */

namespace Amasty\Sorting\Model\Indexer;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Indexer\AbstractProcessor;
use Magento\Framework\Model\ResourceModel\AbstractResource;

class ConfigInvalidateAbstract extends Value
{
    /**
     * @var AbstractProcessor
     */
    private $indexProcessor;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractProcessor $indexProcessor,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->indexProcessor = $indexProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave()
    {
        $this->_getResource()->addCommitCallback([$this, 'processValue']);
        return parent::afterSave();
    }

    /**
     * Invalidate index if Status or Period changed
     *
     * @return void
     */
    public function processValue()
    {
        if ($this->getValue() != $this->getOldValue()) {
            $this->indexProcessor->markIndexerAsInvalid();
        }
    }
}
