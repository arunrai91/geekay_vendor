<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Adapter\Index;

use Amasty\ElasticSearch\Model\Client\ClientInterface;
use Amasty\ElasticSearch\Model\Client\ClientRepositoryInterface;
use Amasty\ElasticSearch\Model\Config;

class IndexNameResolver
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ClientRepositoryInterface
     */
    private $clientRepository;

    /**
     * @var ClientInterface|null
     */
    private $client;

    public function __construct(
        Config $config,
        ClientRepositoryInterface $clientRepository,
        ?ClientInterface $client = null
    ) {
        $this->config = $config;
        $this->clientRepository = $clientRepository;
        $this->client = $client;
    }

    public function getIndexAlias(string $indexerId, int $storeId): string
    {
        return $this->config->getIndexName($indexerId, $storeId);
    }

    public function getIndexName(string $indexerId, int $storeId, array $preparedIndexName = []): string
    {
        // TODO: check original method for Xsearch
        if (!isset($preparedIndexName[$storeId])) {
            $alias = $this->getIndexAlias($indexerId, $storeId);
            $indexName = $this->getIndexNameByAlias($alias, $storeId);

            if (empty($indexName)) {
                $indexName = $alias . '_v1';
            }

            return $indexName;
        }

        return $preparedIndexName[$storeId];
    }

    public function getIndexNameByAlias(string $alias, int $storeId, array $preparedIndexName = []): string
    {
        $storeIndex = '';
        if ($this->getClient()->existsAlias($alias)) {
            $indexPattern = $alias . '_v';

            $alias = $this->getClient()->getAlias($alias);
            $indices = array_keys($alias);
            natsort($indices);

            foreach ($indices as $index) {
                if (strpos($index, $indexPattern) === 0) {
                    if (isset($preparedIndexName[$storeId]) && $preparedIndexName[$storeId] == $index) {
                        continue;
                    }
                    $storeIndex = $index;
                    break;
                }
            }
        }

        return $storeIndex;
    }

    private function getClient(): ?ClientInterface
    {
        if ($this->client === null) {
            $this->client = $this->clientRepository->get();
        }
        return $this->client;
    }
}
