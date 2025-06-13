<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Ui\DataProvider\ActionsLog;

use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;
use Amasty\AdminActionsLog\Model\LogEntry\ResourceModel\Grid\Collection;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\CollectionModifierInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;

class Listing extends DataProvider
{
    /**
     * @var CollectionModifierInterface[]
     */
    private $collectionModifiers;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var Timezone
     */
    private $timezone;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        DateTime $dateTime,
        Timezone $timezone,
        array $collectionModifiers = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
        $this->collectionModifiers = $collectionModifiers;
        $this->dateTime = $dateTime;
        $this->timezone = $timezone;
    }

    public function addFilter(Filter $filter)
    {
        if (isset($this->data['config']['filter_url_params'][$filter->getField()])) {
            // @phpstan-ignore-next-line as adding return statement cause of backward compatibility issue
            return;
        }

        switch ($filter->getField()) {
            case LogEntry::DATE:
                $value = $filter->getValue();
                $date = new \DateTime(
                    $value,
                    new \DateTimeZone($this->timezone->getConfigTimezone())
                );
                $date = $this->dateTime->gmtDate('Y-m-d H:i:s', $date);
                $filter->setValue($date);

                break;
        }

        // @phpstan-ignore-next-line as adding return statement cause of backward compatibility issue
        parent::addFilter($filter);
    }

    public function getSearchResult()
    {
        /** @var Collection $collection */
        $collection = parent::getSearchResult();
        $namespace = $this->request->getParam('namespace');

        if (isset($this->collectionModifiers[$namespace])) {
            $this->collectionModifiers[$namespace]->apply($collection);
        }

        return $collection;
    }

    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $logEntryItems = array_reduce($searchResult->getItems(), function (array $carry, DataObject $item): array {
            $carry[] = $item->getData();

            return $carry;
        }, []);

        return [
            'items' => $logEntryItems,
            'totalRecords' => $searchResult->getTotalCount()
        ];
    }
}
