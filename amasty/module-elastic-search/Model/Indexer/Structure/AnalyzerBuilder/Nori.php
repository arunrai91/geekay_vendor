<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Indexer\Structure\AnalyzerBuilder;

use Amasty\ElasticSearch\Api\Data\Indexer\Structure\AnalyzerBuilderInterface;
use Amasty\ElasticSearch\Model\Indexer\Structure\AnalyzerBuilder\EntityCollectionProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Nori implements AnalyzerBuilderInterface
{
    /**
     * @var EntityCollectionProvider
     */
    private $entityCollectionProvider;

    /**
     * @var \Amasty\ElasticSearch\Model\Config
     */
    private $config;

    public function __construct(
        EntityCollectionProvider $entityCollectionProvider,
        \Amasty\ElasticSearch\Model\Config $config
    ) {
        $this->entityCollectionProvider = $entityCollectionProvider;
        $this->config = $config;
    }

    /**
     * @param int $storeId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function build($storeId)
    {
        $storeId = (int)$storeId;
        $filters = $this->getFilters($storeId);
        $defaultFilters = [];
        if ($readingForm = $this->config->getUseNoriReadingForm($storeId)) {
            $defaultFilters[] = 'nori_readingform';
        }

        $analyzerFilters = $this->sortFilters(
            array_merge($defaultFilters, array_keys($filters), ['lowercase']),
            $storeId
        );
        $tokenizer = $this->getTokenizer($storeId);

        $analyser = [
            'analyzer' => [
                'default' => [
                    'type'      => 'custom',
                    'tokenizer' => key($tokenizer),
                    'filter'    => $analyzerFilters
                ]
            ],
            'tokenizer' => $tokenizer,
            'filter'   => $filters
        ];

        return $analyser;
    }

    private function getFilters($storeId)
    {
        $filters = [
            'stop_filter' => [
                "type" => "stop",
                "stopwords" => $this->getStopWords($storeId),
                'ignore_case' => !$this->config->isStopWordsCaseSensitive($storeId)
            ],
            "graph_synonyms" => [
                "type" => "synonym_graph",
                "lenient" => true,
                "synonyms" => $this->getSynonyms($storeId)
            ]
        ];

        return $filters;
    }

    /**
     * @param int $storeId
     * @return array
     */
    private function getTokenizer($storeId)
    {
        return [
            'nori_custom_tokenizer' => [
                'type' => 'nori_tokenizer',
                'mode' => $this->config->getNoriTokenMode($storeId),
            ]
        ];
    }

    /**
     * @param $storeId
     * @return array|string
     */
    private function getStopWords($storeId)
    {
        $stopWords = [];
        $collection = $this->entityCollectionProvider->getStopWordCollectionFactory()->create();
        $collection->addStoreFilter($storeId);

        foreach ($collection as $stopWord) {
            $stopWords[] = preg_replace('/\s*/u', '-', $stopWord->getTerm());
        }

        if (!count($stopWords)) {
            $stopWords = '_none_';
        }

        return $stopWords;
    }

    /**
     * @param $storeId
     * @return array
     */
    private function getSynonyms($storeId)
    {
        $synonyms = [];
        $collection = $this->entityCollectionProvider->getSynonymCollectionFactory()->create();
        $collection->addStoreFilter($storeId);
        foreach ($collection as $synonym) {
            $synonyms[] = trim($synonym->getTerm());
        }

        return $synonyms ?: ['']; //can't pass empty array to elastic 5.x
    }

    private function sortFilters(array $filters, int $storeId): array
    {
        if (!$this->config->isSynonymsCaseSensitive($storeId)) {
            $lowercaseFilterPosition = array_search('lowercase', $filters, true);
            $synonymFilterPosition = array_search('graph_synonyms', $filters, true);
            if ($lowercaseFilterPosition !== false && $synonymFilterPosition !== false) {
                $temp = array_splice($filters, $lowercaseFilterPosition, 1);
                array_splice($filters, $synonymFilterPosition, 0, $temp);
            }
        }

        return $filters;
    }
}
