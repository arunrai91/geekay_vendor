<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Adapter\Elasticsearch8;

use Amasty\ElasticSearch\Model\Adapter\SaveQueryBuilderInterface;

class SaveQueryBuilder implements SaveQueryBuilderInterface
{
    public function execute(array $documents, string $indexName, string $action = self::BULK_ACTION_INDEX): array
    {
        $bulkArray = [
            'index' => $indexName,
            'body' => [],
            'refresh' => true,
        ];

        foreach ($documents as $id => $document) {
            $bulkArray['body'][] = [
                $action => [
                    '_id' => $id,
                    '_index' => $indexName
                ]
            ];
            if ($action === self::BULK_ACTION_INDEX) {
                $bulkArray['body'][] = $document;
            }
        }

        return $bulkArray;
    }
}
