<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Config;

use Amasty\ElasticSearch\Model\Config as ElasticConfig;
use Amasty\ElasticSearch\Model\Source\FulltextAttributes;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;

class QuerySettingsProcessor
{
    /**
     * @var int[]|string[]
     */
    private $searchableAttributes;

    /**
     * @var array
     */
    private $value;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var FulltextAttributes
     */
    private $fulltextAttributes;

    /**
     * @var Json
     */
    private $serializer;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        FulltextAttributes $fulltextAttributes,
        Json $serializer
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->fulltextAttributes = $fulltextAttributes;
        $this->serializer = $serializer;
    }

    /**
     * @return array|null array(
     *     QuerySettings::WILDCARD => 1|0,
     *     QuerySettings::SPELLING => 1|0,
     *     QuerySettings::COMBINING => 1|0
     * )
     */
    public function getConfigValue(string $attributeCode): ?array
    {
        if (!in_array($attributeCode, $this->getSearchableAttributeCodes(), true)) {
            return null;
        }

        return $this->getValue()[$attributeCode] ?? [
            QuerySettings::WILDCARD => 0,
            QuerySettings::SPELLING => 0,
            QuerySettings::COMBINING => 0
        ];
    }

    public function getSearchableAttributeCodes(): array
    {
        if ($this->searchableAttributes === null) {
            $this->searchableAttributes = array_keys($this->fulltextAttributes->toArray());
        }

        return $this->searchableAttributes;
    }

    private function getValue(): array
    {
        if ($this->value === null) {
            $value = $this->scopeConfig->getValue(
                ElasticConfig::ELASTIC_SEARCH_ENGINE . '/catalog/query_settings',
                ScopeInterface::SCOPE_STORE
            );
            $this->value = $this->parseLoad($value);
        }

        return $this->value;
    }

    public function parseLoad($value)
    {
        $value = $this->unserializeValue($value);

        return $this->convertConfigurations($value);
    }

    private function unserializeValue($value)
    {
        $result = [];
        if (is_string($value) && !empty($value)) {
            $result = $this->serializer->unserialize($value);
        }

        return $result;
    }

    /**
     * Convert configuration from storable format to usable
     *
     * Older version of the module didn't have uniq storable format.
     * Method have resolver for that
     */
    private function convertConfigurations(array $configurations): array
    {
        $attributeCodes = $this->getSearchableAttributeCodes();
        $result = [];
        foreach ($configurations as $code => $row) {
            if (in_array($code, $attributeCodes, true)) {
                $result[$code] = [
                    QuerySettings::WILDCARD => $row[QuerySettings::KEY_WILDCARD] ?? $row[QuerySettings::WILDCARD] ?? 0,
                    QuerySettings::SPELLING => $row[QuerySettings::KEY_SPELLING] ?? $row[QuerySettings::SPELLING] ?? 0,
                    QuerySettings::COMBINING => $row[QuerySettings::KEY_COMBINING] ??
                            $row[QuerySettings::COMBINING] ?? 0,
                ];
            }
        }

        return $result;
    }
}
