<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Ui\RelevanceRule\Listing;

use Magento\Framework\Api\Search\SearchResultInterface;
use Amasty\ElasticSearch\Api\Data\RelevanceRuleInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider as MagentoDataProvider;

class DataProvider extends MagentoDataProvider
{
    /**
     * @param SearchResultInterface $searchResult
     * @return array
     */
    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $result = [
            'items' => [],
            'totalRecords' => $searchResult->getTotalCount(),
        ];

        /** @var RelevanceRuleInterface $item */
        foreach ($searchResult->getItems() as $item) {
            $result['items'][] = $item->getData();
        }

        return $result;
    }
}
