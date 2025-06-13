<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Adapter\Elasticsearch8;

use Amasty\ElasticSearch\Model\Adapter\FieldMappingBuilderInterface;
use Amasty\ElasticSearch\Model\Config;
use Amasty\ElasticSearch\Model\Indexer\Structure\DynamicTemplateMapper;
use Amasty\ElasticSearch\Model\Indexer\Structure\EntityBuilder\Product;

class FieldMappingBuilder implements FieldMappingBuilderInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var DynamicTemplateMapper
     */
    private $dynamicTemplateMapper;

    public function __construct(Config $config, DynamicTemplateMapper $dynamicTemplateMapper)
    {
        $this->config = $config;
        $this->dynamicTemplateMapper = $dynamicTemplateMapper;
    }

    public function execute(string $index, int $storeId, array $fields): array
    {
        $params = [
            'index' => $index,
            'body' => [
                'properties' => [],
                'dynamic_templates' => [
                    $this->dynamicTemplateMapper->map($storeId)
                ],
                'numeric_detection' => false,
            ],
        ];

        $isAllowSpecialChars = $this->config->allowSpecialChars($storeId);
        $hasStemming = $this->config->hasStemming($storeId);
        foreach ($fields as $field => $fieldInfo) {
            if ($fieldInfo['type'] === Product::ATTRIBUTE_TYPE_TEXT) {
                $fieldInfo['fielddata'] = true;

                if ($isAllowSpecialChars) {
                    $fieldInfo['analyzer'] = 'allow_spec_chars';
                }

                if ($hasStemming) {
                    $fieldInfo['fields']['stemming'] = [
                        'type' => Product::ATTRIBUTE_TYPE_TEXT,
                        'fielddata' => true,
                        'analyzer' => 'stem',
                        'index' => true
                    ];
                }
            }

            $params['body']['properties'][$field] = $fieldInfo;
        }
        $params['include_type_name'] = true;

        return $params;
    }
}
