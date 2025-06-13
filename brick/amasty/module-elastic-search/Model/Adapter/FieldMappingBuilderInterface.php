<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Adapter;

interface FieldMappingBuilderInterface
{
    /**
     * @param string $index
     * @param array $fields
     * @param int $storeId
     * @return array
     */
    public function execute(string $index, int $storeId, array $fields): array;
}
