<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Client\Elasticsearch8;

use Amasty\ElasticSearch\Exception\Missing404Exception as AmastyMissing404Exception;
use Amasty\ElasticSearch\Exception\NoNodesAvailableException as AmastyNoNodesAvailableException;
use Amasty\ElasticSearch\Exception\SystemPackageNotInstalled;
use Amasty\ElasticSearch\Model\Client\ClientInterface;
use Elastic\Elasticsearch\Client as ElasticsearchClient;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Magento\Framework\Exception\LocalizedException;

class Client implements ClientInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var ElasticsearchClient[] ['pid' => client, ...]
     */
    private $client = [];

    /**
     * @var string|null
     */
    private $elasticEngineVersion;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function query(array $query): array
    {
        return $this->getElasticsearchClient()->search($query)->asArray();
    }

    public function search(array $query): array
    {
        try {
            return $this->getElasticsearchClient()->search($query)->asArray();
        } catch (ClientResponseException $e) {
            $message = __('Elasticsearch index not found. Run "bin/magento indexer:reindex catalogsearch_fulltext".');
            throw new AmastyMissing404Exception($message->render(), 0, $e);
        }
    }

    /**
     * @throws AmastyMissing404Exception
     */
    public function count(array $query): int
    {
        try {
            return $this->getElasticsearchClient()->count($query)->asArray()['count'] ?? 0;
        } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
            $message = __('Elasticsearch index not found. Run "bin/magento indexer:reindex catalogsearch_fulltext".');
            throw new AmastyMissing404Exception($message->render(), 0, $e);
        }
    }

    /**
     * TODO: check
     */
    public function bulkQuery(array $query): array
    {
        return $this->getElasticsearchClient()->bulk($query)->asArray();
    }

    public function isEmptyIndex(string $index): bool
    {
        $stats = $this->getElasticsearchClient()->indices()->stats(['index' => $index, 'metric' => 'docs'])->asArray();
        return !isset($stats['indices'][$index]) || $stats['indices'][$index]['primaries']['docs']['count'] === 0;
    }

    public function createIndex(string $index, array $settings): void
    {
        $params = [
            'index' => $index,
            'body' => []
        ];
        if (!empty($settings)) {
            $params['body']['settings'] = $settings;
        }

        $this->getElasticsearchClient()->indices()->create($params);
    }

    public function deleteIndex(string $index): void
    {
        $this->getElasticsearchClient()->indices()->delete(['index' => $index]);
    }

    public function indexExists(string $index): bool
    {
        return $this->getElasticsearchClient()->indices()->exists(['index' => $index])->asBool();
    }

    /**
     * TODO: check
     */
    public function existsAlias(string $alias, string $index = ''): bool
    {
        $params = ['name' => $alias];
        if ($index) {
            $params['index'] = $index;
        }

        try {
            return $this->getElasticsearchClient()->indices()->existsAlias($params)->asBool();
        } catch (NoNodeAvailableException $e) {
            $message = __('Elasticsearch server not found. Check "Stores > Configuration'
                . ' > Amasty Elastic Search > Connection" and make sure that "Test Connection"'
                .' is successfull with appropriate host name and port.');
            throw new AmastyNoNodesAvailableException($message->render(), 0, $e);
        }
    }

    public function getAlias(string $alias): array
    {
        return $this->getElasticsearchClient()->indices()->getAlias(['name' => $alias])->asArray();
    }

    public function updateAlias(string $alias, string $newIndex): void
    {
        $params['body']['actions'][] = ['add' => ['alias' => $alias, 'index' => $newIndex]];
        $this->updateAliases($params);
    }

    public function updateAliases(array $params): void
    {
        $this->getElasticsearchClient()->indices()->updateAliases($params);
    }

    public function putMapping(array $params): void
    {
        $this->getElasticsearchClient()->indices()->putMapping($params);
    }

    /**
     * @throws SystemPackageNotInstalled
     */
    public function ping(): bool
    {
        try {
            return $this->getElasticsearchClient()->ping(
                ['client' => ['timeout' => $this->options['timeout']]]
            )->asBool();
        } catch (SystemPackageNotInstalled $e) {
            throw $e;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function isSystemPackageAvailable(): bool
    {
        return class_exists(ClientBuilder::class);
    }

    public function getNeededSystemPackageVersion(): string
    {
        return '~8.5.0';
    }

    public function getSystemPackageName(): string
    {
        return 'elasticsearch/elasticsearch';
    }

    public function getCurrentEngineVersion(): string
    {
        if ($this->elasticEngineVersion === null) {
            $elasticInfo = $this->getElasticsearchClient()->info()->asArray();
            $this->elasticEngineVersion = $elasticInfo['version']['number'] ?? null;
        }

        return $this->elasticEngineVersion;
    }

    private function getElasticsearchClient(): ElasticsearchClient
    {
        $pid = getmypid();
        if (!isset($this->client[$pid])) {
            $this->validateClient();
            $this->client[$pid] = ClientBuilder::fromConfig($this->options, true);
        }

        return $this->client[$pid];
    }

    /**
     * @throws SystemPackageNotInstalled
     */
    private function validateClient(): void
    {
        if (!$this->isSystemPackageAvailable()) {
            throw new SystemPackageNotInstalled(__(
                'Elasticsearch package is not installed. Please, run the following command
                    in the SSH: composer require %1 %2 -W',
                $this->getSystemPackageName(),
                $this->getNeededSystemPackageVersion()
            ));
        }
    }
}
