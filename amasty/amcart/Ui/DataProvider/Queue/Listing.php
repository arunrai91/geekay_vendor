<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Ui\DataProvider\Queue;

use Amasty\Acart\Model\History;
use Amasty\Acart\Model\ResourceModel\History\CollectionFactory;
use Amasty\Acart\Model\RuleQuote;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Api\Filter;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

class Listing extends AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var AddFieldToCollectionInterface[]
     */
    protected $addFieldStrategies;

    /**
     * @var AddFilterToCollectionInterface[]
     */
    protected $addFilterStrategies;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create()
            ->addRuleQuoteData()
            ->addRuleData()
            ->addFieldToFilter('ruleQuote.status', RuleQuote::STATUS_PROCESSING)
            ->addFieldToFilter('main_table.status', History::STATUS_PROCESSING);
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
    }

    public function addField($field, $alias = null)
    {
        if (isset($this->addFieldStrategies[$field])) {
            $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);
        } else {
            parent::addField($field, $alias);
        }
    }

    public function addFilter(Filter $filter)
    {
        // @phpstan-ignore-next-line as adding return statement cause of backward compatibility issue
        if (isset($this->addFilterStrategies[$filter->getField()])) {
            $this->addFilterStrategies[$filter->getField()]
                ->addFilter(
                    $this->getCollection(),
                    $filter->getField(),
                    [$filter->getConditionType() => $filter->getValue()]
                );
        } else {
            parent::addFilter($filter);
        }
    }
}
