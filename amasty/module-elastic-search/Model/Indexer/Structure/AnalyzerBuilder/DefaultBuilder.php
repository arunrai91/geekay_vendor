<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Indexer\Structure\AnalyzerBuilder;

use Amasty\ElasticSearch\Api\Data\Indexer\Structure\AnalyzerBuilderInterface;
use Amasty\ElasticSearch\Model\Client\ClientRepositoryInterface;
use Amasty\ElasticSearch\Model\Config;

class DefaultBuilder implements AnalyzerBuilderInterface
{
    public const WORD_DELIMITER_VERSION_5 = 'word_delimiter';
    public const WORD_DELIMITER_VERSION_6 = 'word_delimiter_graph';
    public const LOWERCASE_NORMALIZER = 'useLowercase';

    /**
     * @var EntityCollectionProvider
     */
    private $entityCollectionProvider;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ClientRepositoryInterface
     */
    private $clientRepository;

    public function __construct(
        EntityCollectionProvider $entityCollectionProvider,
        Config $config,
        ClientRepositoryInterface $clientRepository
    ) {
        $this->entityCollectionProvider = $entityCollectionProvider;
        $this->config = $config;
        $this->clientRepository = $clientRepository;
    }

    /**
     * @param int $storeId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function build($storeId)
    {
        $storeId = (int)$storeId;
        $analyser = [
            'analyzer' => [
                //"the A*b-1^2 O'Neil's" => ["ab12", "oneil"]
                'default' => [
                    'type'      => 'custom',
                    'tokenizer' => $this->config->getTokenizer($storeId),
                    'filter'    => $this->sortFilters([
                        'stop_filter',
                        "graph_synonyms",
                        'lowercase',
                        $this->getWordDelimiterFilter()
                    ], $storeId),
                ],
                //"the A*b-1^2 O'Neil's" => ["a*b-1^2", "o'neil's"]
                'allow_spec_chars' => [
                    'type'      => 'custom',
                    'tokenizer' => $this->config->getTokenizer($storeId),
                    'filter'    => $this->sortFilters([
                        'stop_filter',
                        'graph_synonyms',
                        'lowercase'
                    ], $storeId),
                ],
                'stem' => [
                    'type'      => 'custom',
                    'tokenizer' => $this->config->getTokenizer($storeId),
                    'filter'    => [
                        'stop_filter',
                        'lowercase'
                    ]
                ]
            ],
            'filter'   => [
                $this->getWordDelimiterFilter() => [
                    // https://www.elastic.co/guide/en/elasticsearch/reference/current/analysis-word-delimiter-graph-tokenfilter.html
                    'type'                    => $this->getWordDelimiterFilter(),
                    'catenate_all'            => true,
                    'catenate_words'          => false,
                    'catenate_numbers'        => false,
                    //^ catenate all
                    'generate_word_parts'     => false,
                    'generate_number_parts'   => false,
                    'split_on_case_change'    => false,
                    'preserve_original'       => true,
                    'split_on_numerics'       => false,
                ],
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
            ],
            "normalizer" => [
                self::LOWERCASE_NORMALIZER => [
                    "type" => "custom",
                    "filter" => ["lowercase"]
                ]
            ]
        ];

        if ($stemmingData = $this->getStemmingData($storeId)) {
            $analyser['filter']['stemming'] = [
                'type' => 'stemmer',
                'language' => $stemmingData
            ];

            $analyser['analyzer']['stem']['filter'][] = 'stemming';
        }

        $charMappings = $this->config->getCharMappings($storeId);
        if ($charMappings) {
            $analyser['char_filter']['mapped_char_filter'] = [
                'type' => 'mapping',
                'mappings' => $charMappings
            ];
            $analyser['analyzer']['default']['char_filter'] = 'mapped_char_filter';
            $analyser['analyzer']['allow_spec_chars']['char_filter'] = 'mapped_char_filter';
        }

        return $analyser;
    }

    /**
     * @param $storeId
     * @return array|string
     */
    private function getStopWords($storeId)
    {
        $usePredefined = $this->config->getUsePredefinedStopwords($storeId);
        if ($usePredefined) {
            return sprintf(
                '_%s_',
                $this->config->getStopWordsLanguage($storeId)
            );
        } else {
            $stopWords = [];
            $collection = $this->entityCollectionProvider->getStopWordCollectionFactory()->create();
            $collection->addStoreFilter($storeId);
            foreach ($collection as $stopWord) {
                $stopWords[] = preg_replace('/\s*/', '', $stopWord->getTerm());
            }
            if (!count($stopWords)) {
                $stopWords = '_none_';
            }
        }

        return $stopWords;
    }

    /**
     * @param $storeId
     * @return string
     */
    private function getStemmingData($storeId)
    {
        $usePredefined = $this->config->getUsePredefinedStemming($storeId);
        if ($usePredefined) {
            return $this->config->getStemmedLanguage($storeId);
        }

        return '';
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

    /**
     * @return string
     */
    private function getWordDelimiterFilter()
    {
        $elasticVersion = $this->clientRepository->get()->getCurrentEngineVersion();

        if (version_compare($elasticVersion, '5.0.0', '>=')
            && version_compare($elasticVersion, '6.0.0', '<=')
        ) {
            return self::WORD_DELIMITER_VERSION_5;
        }

        return self::WORD_DELIMITER_VERSION_6;
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
