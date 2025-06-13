<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Api\Data\Indexer\Data;

interface DataMapperInterface
{
    public const ENTITY_ID_FIELD_NAME = 'entity_id';

    /**
     * @since 1.17.1 introduced mapped data in $context['documents']
     *
     * @param array $indexData
     * @param int $storeId
     * @param array{ 'indexer_id': string, 'document': array{int: array|string|int} } $context
     * @return array
     */
    public function map(array $indexData, $storeId, array $context = []);
}
