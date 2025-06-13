<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Adapter\Elasticsearch7;

use Amasty\ElasticSearch\Model\Adapter\SaveQueryBuilderInterface;
use Amasty\ElasticSearch\Model\Config;

class SaveQueryBuilder implements SaveQueryBuilderInterface
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function execute(array $documents, string $indexName, string $action = self::BULK_ACTION_INDEX): array
    {
        $entityType = $this->config->getEntityType();

        $bulkArray = [
            'index' => $indexName,
            'type' => $entityType,
            'body' => [],
            'refresh' => true,
        ];

        foreach ($documents as $id => $document) {
            $bulkArray['body'][] = [
                $action => [
                    '_id' => $id,
                    '_type' => $entityType,
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
