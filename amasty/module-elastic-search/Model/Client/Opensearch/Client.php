<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Client\Opensearch;

use Amasty\ElasticSearch\Exception\Missing404Exception as AmastyMissing404Exception;
use Amasty\ElasticSearch\Exception\NoNodesAvailableException as AmastyNoNodesAvailableException;
use Amasty\ElasticSearch\Exception\SystemPackageNotInstalled;
use Amasty\ElasticSearch\Model\Client\ClientInterface;
use OpenSearch\Client as OpenSearchClient;
use OpenSearch\ClientBuilder;
use OpenSearch\Common\Exceptions\Missing404Exception;
use OpenSearch\Common\Exceptions\NoNodesAvailableException;

class Client implements ClientInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var OpenSearchClient[] ['pid' => client, ...]
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
        unset($query['type']);
        return $this->getOpenSearchClient()->search($query);
    }

    public function search(array $query): array
    {
        try {
            unset($query['type']);
            return $this->getOpenSearchClient()->search($query);
        } catch (Missing404Exception $e) {
            $message = __('Opensearch index not found. Run "bin/magento indexer:reindex catalogsearch_fulltext".');
            throw new AmastyMissing404Exception($message, 0, $e);
        }
    }

    /**
     * @throws AmastyMissing404Exception
     */
    public function count(array $query): int
    {
        try {
            unset($query['type']);
            return $this->getOpenSearchClient()->count($query)['count'] ?? 0;
        } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
            $message = __('Opensearch index not found. Run "bin/magento indexer:reindex catalogsearch_fulltext".');
            throw new AmastyMissing404Exception($message->render(), 0, $e);
        }
    }

    public function bulkQuery(array $query): array
    {
        return $this->getOpenSearchClient()->bulk($query);
    }

    public function isEmptyIndex(string $index): bool
    {
        $stats = $this->getOpenSearchClient()->indices()->stats(['index' => $index, 'metric' => 'docs']);
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

        $this->getOpenSearchClient()->indices()->create($params);
    }

    public function deleteIndex(string $index): void
    {
        $this->getOpenSearchClient()->indices()->delete(['index' => $index]);
    }

    public function indexExists(string $index): bool
    {
        return $this->getOpenSearchClient()->indices()->exists(['index' => $index]);
    }

    public function existsAlias(string $alias, string $index = ''): bool
    {
        $params = ['name' => $alias];
        if ($index) {
            $params['index'] = $index;
        }

        try {
            return $this->getOpenSearchClient()->indices()->existsAlias($params);
        } catch (NoNodesAvailableException $e) {
            $message = __('Opensearch server not found. Check "Stores > Configuration'
                . ' > Amasty Elastic Search > Connection" and make sure that "Test Connection"'
                .' is successfull with appropriate host name and port.');
            throw new AmastyNoNodesAvailableException($message, 0, $e);
        }
    }

    public function getAlias(string $alias): array
    {
        return $this->getOpenSearchClient()->indices()->getAlias(['name' => $alias]);
    }

    public function updateAlias(string $alias, string $newIndex): void
    {
        $params['body']['actions'][] = ['add' => ['alias' => $alias, 'index' => $newIndex]];
        $this->updateAliases($params);
    }

    public function updateAliases(array $params): void
    {
        $this->getOpenSearchClient()->indices()->updateAliases($params);
    }

    public function putMapping(array $params): void
    {
        $this->getOpenSearchClient()->indices()->putMapping($params);
    }

    /**
     * @throws SystemPackageNotInstalled
     */
    public function ping(): bool
    {
        try {
            return $this->getOpenSearchClient()->ping(
                ['client' => ['timeout' => $this->options['timeout']]]
            );
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
        return '2.0.0';
    }

    public function getSystemPackageName(): string
    {
        return 'opensearch-project/opensearch-php';
    }

    public function getCurrentEngineVersion(): string
    {
        if ($this->elasticEngineVersion === null) {
            $elasticInfo = $this->getOpenSearchClient()->info();
            $this->elasticEngineVersion = $elasticInfo['version']['number'] ?? null;
        }

        return $this->elasticEngineVersion;
    }

    /**
     * @throws SystemPackageNotInstalled
     */
    private function getOpenSearchClient(): OpenSearchClient
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
                    in the SSH: composer require %1 ^%2',
                $this->getSystemPackageName(),
                $this->getNeededSystemPackageVersion()
            ));
        }
    }
}
