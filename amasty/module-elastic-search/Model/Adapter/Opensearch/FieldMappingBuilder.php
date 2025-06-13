<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Adapter\Opensearch;

use Amasty\ElasticSearch\Model\Adapter\Elasticsearch8\FieldMappingBuilder as Elasticsearch8FieldMappingBuilder;
use Amasty\ElasticSearch\Model\Adapter\FieldMappingBuilderInterface;

class FieldMappingBuilder implements FieldMappingBuilderInterface
{
    /**
     * @var Elasticsearch8FieldMappingBuilder
     */
    private $fieldMappingBuilder;

    public function __construct(Elasticsearch8FieldMappingBuilder $fieldMappingBuilder)
    {
        $this->fieldMappingBuilder = $fieldMappingBuilder;
    }

    public function execute(string $index, int $storeId, array $fields): array
    {
        $result = $this->fieldMappingBuilder->execute($index, $storeId, $fields);
        unset($result['include_type_name']);

        return $result;
    }
}
