<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\Search;

use Amasty\Xsearch\Block\Search\Product;
use Amasty\Xsearch\Helper\Data;
use Amasty\Xsearch\Model\Adapter\Product as ProductAdapter;
use Amasty\Xsearch\Model\Client\Factory;
use Amasty\Xsearch\Model\IsElasticEngine;
use Amasty\Xsearch\Model\Search\SearchResultFactory;
use Amasty\Xsearch\Model\Config;
use Amasty\Xsearch\Model\Indexer\ElasticExternalProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Search\Model\QueryInterface;
use Magento\Store\Model\StoreManagerInterface;

class SearchAdapterResolver
{
    /**
     * @var array
     */
    private $results = [];

    /**
     * @var GetRequestQuery
     */
    private $getRequestQuery;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $indexedTypes;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Factory
     */
    private $clientFactory;

    /**
     * @var ProductAdapter
     */
    private $productAdapter;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var SearchResultFactort
     */
    private $searchResultFactory;
    /**
     * @var IsElasticEngine|null
     */
    private $isElasticEngine;

    public function __construct(
        GetRequestQuery $getRequestQuery,
        StoreManagerInterface $storeManager,
        Config $config,
        Factory $clientFactory,
        ProductAdapter $productAdapter,
        Data $helper,
        SearchResultFactory $searchResultFactory,
        array $indexedTypes = [],
        ?IsElasticEngine $isElasticEngine = null
    ) {
        $this->getRequestQuery = $getRequestQuery;
        $this->storeManager = $storeManager;
        $this->indexedTypes = $indexedTypes;
        $this->config = $config;
        $this->clientFactory = $clientFactory;
        $this->helper = $helper;
        $this->productAdapter = $productAdapter;
        $this->searchResultFactory = $searchResultFactory;
        $this->isElasticEngine = $isElasticEngine ?? ObjectManager::getInstance()->get(IsElasticEngine::class);
    }

    public function getResults(string $type, QueryInterface $query): ?SearchResult
    {
        $isCanUseIndex = $this->config->isEnablePopupIndex()
            && $this->isElasticEngine->execute()
            && ($type !== Product::BLOCK_TYPE || $this->config->isAmastyElasticEngine());
        $isAcceptableType = in_array($type, $this->indexedTypes);
        $result = $isCanUseIndex && $isAcceptableType ? $this->getResultsFromIndex($type, $query) : null;

        if ($result !== null) {
            [$items, $resultCount] = $result;
            $items = $this->prepareResponse($items, $type, $query->getQueryText());
            $searchResult = $this->searchResultFactory->create();
            $searchResult->setItems($items);
            $searchResult->setResultsCount($resultCount);
        }

        return $searchResult ?? null;
    }

    private function getResultsFromIndex(string $type, QueryInterface $query): array
    {
        $query = $query->getQueryText();
        $query = str_replace('~', '', $query);
        $results = [];
        $resultsTotalCount = 0;

        if ($type === Product::BLOCK_TYPE) {
            if ($this->config->isAmastyElasticEngine()) {
                $results = $this->productAdapter->getProductIndex($query, $type);
                $resultsTotalCount = $results['hits'] ?? 0;
                $results = $results['products'] ?? [];
            }
        } else {
            $results = $this->getIndexedItems($query, $type);
            $resultsTotalCount = count($results);
        }

        return [$results, $resultsTotalCount];
    }

    /**
     * @param string $searchQuery
     * @param string $indexType
     * @return mixed
     * @throws \Elasticsearch\Common\Exceptions\Missing404Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getIndexedItems(string $searchQuery, string $indexType)
    {
        if (!isset($this->results[$searchQuery])) {
            $searchQuery = str_replace('"', '\"', preg_quote($searchQuery, '/'));
            foreach ($this->indexedTypes as $label) {
                $this->results[$searchQuery][$label] = [];
            }

            $client = $this->clientFactory->getClient();
            if ($client) {
                $elasticResponse = $client->query($this->modifyQuery($searchQuery));
                $documents = [];
                if (isset($elasticResponse['hits']['hits'])) {
                    $documents = array_map(function ($item) {
                        return $item['_source'];
                    }, $elasticResponse['hits']['hits']);
                }

                foreach ($documents as $document) {
                    $type = $document[ElasticExternalProvider::BLOCK_TYPE_FIELD];
                    unset(
                        $document[ElasticExternalProvider::BLOCK_TYPE_FIELD],
                        $document[ElasticExternalProvider::FULLTEXT_INDEX_FIELD]
                    );

                    $this->results[$searchQuery][$type][] = $document;
                }
            }
        }

        return $this->results[$searchQuery][$indexType] ?? [];
    }

    private function modifyQuery(string $searchQuery): array
    {
        $queryArray = array_map(function ($item) {
            return mb_strlen($item) > 2 ? $item . '*' : $item;
        }, array_filter(explode(' ', $searchQuery)));
        $elasticQuery = implode(' OR ', $queryArray);

        return $this->getRequestQuery->executeExternalByFulltext(
            $elasticQuery,
            (int) $this->storeManager->getStore()->getId(),
            ElasticExternalProvider::FULLTEXT_INDEX_FIELD,
            \Amasty\Xsearch\Controller\RegistryConstants::POPUP_INDEXER_ID
        );
    }

    private function prepareResponse(array $response, string $type, string $queryText): array
    {
        if ($limit = (int) $this->config->getModuleConfig($type . '/limit')) {
            $response = array_slice($response, 0, $limit, true);
        }

        if ($type === Product::BLOCK_TYPE) {
            $response = $this->productAdapter->sortProducts($response);
        }

        foreach ($response as &$item) {
            if (isset($item['sku'])) {
                $item['sku'] = $this->helper->highlight($item['sku'], $queryText);
            }

            if (isset($item['name'])) {
                $item['name'] = $this->helper->highlight($item['name'], $queryText);
            }

            if (isset($item['title'])) {
                $item['title'] = $this->helper->highlight($item['title'], $queryText);
            }

            if (isset($item['description'])) {
                $item['description'] = $this->helper->highlight($item['description'], $queryText);
            }
        }

        return $response;
    }
}
