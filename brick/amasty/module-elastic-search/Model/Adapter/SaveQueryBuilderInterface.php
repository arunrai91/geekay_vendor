<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Adapter;

interface SaveQueryBuilderInterface
{
    public const BULK_ACTION_INDEX = 'index';
    public const BULK_ACTION_DELETE = 'delete';

    /**
     * @param array $documents
     * @param string $indexName
     * @param string $action
     * @return array
     */
    public function execute(array $documents, string $indexName, string $action = self::BULK_ACTION_INDEX): array;
}
