<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Search\GetRequestQuery;

use Amasty\ElasticSearch\Model\Config\QuerySettings;
use Amasty\ElasticSearch\Model\GetNonTextAttributes;
use Amasty\ElasticSearch\Model\Source\WildcardMode;
use Magento\Framework\Search\Request\QueryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Store\Model\StoreManager;
use Amasty\ElasticSearch\Model\ResourceModel\StopWord\CollectionFactory;

class InjectMatchQuery implements InjectSubqueryInterface
{
    private const MIN_CHAR_FOR_MORE_SPELLING_CORRECTION = 6;

    /**
     * @var \Amasty\ElasticSearch\Model\Config
     */
    private $config;

    /**
     * @var GetNonTextAttributes
     */
    private $getNonTextAttributes;

    /**
     * @var ServicePreprocessor\PreprocessorInterface[]
     */
    private $services;

    /**
     * @var AttributeCollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var CollectionFactory
     */
    private $stopWordCollectionFactory;

    /**
     * @var array
     */
    private $selectAttributes = [];

    /**
     * @var array
     */
    private $excludedAttributes = [];

    /**
     * @var null
     */
    private $stopWords = null;

    public function __construct(
        \Amasty\ElasticSearch\Model\Config $config,
        GetNonTextAttributes $getNonTextAttributes,
        AttributeCollectionFactory $attributeCollectionFactory,
        StoreManager $storeManager,
        CollectionFactory $collectionFactory,
        array $services = []
    ) {
        $this->services = $services;
        $this->getNonTextAttributes = $getNonTextAttributes;
        $this->config = $config;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->storeManager = $storeManager;
        $this->stopWordCollectionFactory = $collectionFactory;
        $this->_construct();
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $attributeCollection = $this->attributeCollectionFactory->create();
        $attributeCollection
            ->getSelect()
            ->setPart('columns', [])
            ->columns(['frontend_input', 'attribute_code']);
        foreach ($attributeCollection as $attribute) {
            if (in_array($attribute->getFrontendInput(), ['select', 'multiselect'], true)) {
                $this->selectAttributes[] = $attribute->getAttributeCode();
            } elseif ($attribute->getFrontendInput() === 'boolean') {
                $this->excludedAttributes[] = $attribute->getAttributeCode();
            }
        }

        $this->excludedAttributes = array_merge($this->excludedAttributes, $this->getNonTextAttributes->execute());
    }

    /**
     * @inheritdoc
     */
    public function execute(array $elasticQuery, QueryInterface $request, $conditionType)
    {
        $requestValue = $this->processServices($request->getValue());
        $requestValue = ['condition' => $conditionType, 'value' => $requestValue];
        $conditionQuery = $this->getConditionsByMatches($request, $requestValue);
        foreach ($conditionQuery as $subCondition) {
            $elasticQuery['bool'][$subCondition['condition']][]= $subCondition['body'];
        }

        return $elasticQuery;
    }

    /**
     * @param string $requestValue
     * @return string
     */
    private function processServices($requestValue)
    {
        $requestValue = strip_tags($requestValue);
        $pattern = '/(\+|-|\/|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/';
        $requestValue = preg_replace($pattern, '\\\$1', $requestValue);
        foreach ($this->services as $service) {
            $requestValue = $service->process($requestValue);
        }

        return $requestValue;
    }

    private function getConditionsByMatches(QueryInterface $request, array $requestValue): array
    {
        $conditions = [];

        foreach ($request->getMatches() as $match) {
            $queryConfig = $this->config->getQuerySettingByAttributeCode($match['field']);

            if (in_array($match['field'], $this->excludedAttributes, true) || empty($queryConfig)) {
                continue;
            }

            $field = $this->getFieldName($match['field']);
            $value = $this->getValue($requestValue['value'], $queryConfig);
            $conditions[] = $this->getCondition($field, $value, $match, $requestValue, $queryConfig);

            if ($queryConfig[QuerySettings::WILDCARD]) {
                $value = $this->getValue($requestValue['value'], $queryConfig, true);
                $conditions[] = $this->getCondition($field, $value, $match, $requestValue, $queryConfig);
            }

            if ($this->config->hasStemming()) {
                $stemmingValue = $this->getValue($requestValue['value'], $queryConfig, true);
                $conditions[] = $this->getCondition(
                    $field,
                    $stemmingValue,
                    $match,
                    $requestValue,
                    $queryConfig,
                    true
                );
            }
        }

        return $conditions;
    }

    private function getCondition(
        string $field,
        string $value,
        array $match,
        array $requestValue,
        array $queryConfig,
        bool $hasStemming = false
    ): array {
        $stemming = $hasStemming ? '.stemming' : '';

        return [
            'body' => [
                'query_string' => [
                    'default_field' => $field . $stemming,
                    'query' => $value,
                    'boost' => pow(2, $match['boost'] ?? 1),
                    'default_operator' => $queryConfig[QuerySettings::COMBINING] ? 'AND' : 'OR'
                ],
            ],
            'condition' => $requestValue['condition']
        ];
    }

    /**
     * @param string $name
     * @return string
     */
    private function getFieldName($name)
    {
        if (in_array($name, $this->selectAttributes, true)) {
            $name .= '_value';
        }

        return $name;
    }

    private function getValue(
        string $value,
        array $queryConfig,
        bool $skipWildCardModification = false
    ): string {
        $wildcardType = (string)$this->config->getModuleConfig('catalog/wildcard_mode');
        $wildMinChars = (int)$this->config->getModuleConfig('catalog/wildcard_symbols');
        $spellMinChars = (int)$this->config->getModuleConfig('catalog/spellcorrection_symbols');
        $wildcard = (bool)$queryConfig[QuerySettings::WILDCARD];
        $spellCorrection = (bool)$queryConfig[QuerySettings::SPELLING];
        $value = array_filter(explode(' ', $value));
        $stopWords = $this->getStopWords($value);
        $queryWords = array_udiff(
            $value,
            $stopWords,
            $this->config->isStopWordsCaseSensitive() ? 'strcmp' : 'strcasecmp'
        );

        if (!$this->config->useCustomAnalyzer($this->storeManager->getStore()->getId())
            && !$skipWildCardModification
        ) {
            $queryWords = $this->processQueryWords(
                $queryWords,
                $wildcard,
                $wildMinChars,
                $wildcardType,
                $spellCorrection,
                $spellMinChars
            );
        }

        return implode(' ', $queryWords);
    }

    /**
     * @param array $words
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function removeStopWords($words = [])
    {
        $stopWords = $this->getStopWords($words);
        foreach ($words as $key => $word) {
            if (trim($word) !== '' && $this->insensitiveInArray($word, $stopWords, true)) {
                unset($words[$key]);
            }
        }

        return $words;
    }

    /**
     * @param array $words
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getStopWords(array $words = [])
    {
        $storeId = $this->storeManager->getStore()->getId();
        if ($this->stopWords === null
            && !$this->config->getUsePredefinedStopwords($storeId)
        ) {
            $this->stopWords = [];
            $words = array_diff($words, [';']);//fix multiple query error
            $collection = $this->stopWordCollectionFactory->create()
                ->addStoreFilter($storeId)
                ->addTermsFilter($words);
            foreach ($collection as $stopWord) {
                $this->stopWords[$stopWord->getId()] = $stopWord->getTerm();
            }
        }

        return $this->stopWords ? : [];
    }

    /**
     * @param $needle
     * @param $haystack
     * @param $strict
     * @return bool
     */
    private function insensitiveInArray($needle, $haystack, $strict)
    {
        return in_array(strtolower($needle), array_map('strtolower', $haystack), $strict);
    }

    private function processQueryWords(
        array $queryWords,
        bool $wildcard,
        int $wildMinChars,
        string $wildcardType,
        bool $spellCorrection,
        int $spellMinChars
    ): array {
        foreach ($queryWords as &$term) {
            if ($wildcard && (mb_strlen($term) >= $wildMinChars)) {
                switch ($wildcardType) {
                    case WildcardMode::BOTH:
                        $term = '*' . $term . '*';
                        break;
                    case WildcardMode::PREFIX:
                        $term = '*' . $term;
                        break;
                    case WildcardMode::SUFFIX:
                        $term .= '*';
                        break;
                }
            } elseif ($spellCorrection && (mb_strlen($term) >= $spellMinChars)) {
                $term .= mb_strlen($term) >= self::MIN_CHAR_FOR_MORE_SPELLING_CORRECTION ? '~2' : '~1';
            }
        }

        return $queryWords;
    }
}
