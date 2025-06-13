<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Api\Data\Indexer\Data;

interface DataMapperResolverInterface
{
    /**
     * @param int $entityId
     * @param array $indexData
     * @param int $storeId
     * @param array $context
     * @return array
     */
    public function mapEntityData(array $indexData, $storeId, $context = []);
}
