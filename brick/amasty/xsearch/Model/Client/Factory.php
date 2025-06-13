<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\Client;

use Amasty\ElasticSearch\Model\Client\ClientInterface;
use Amasty\Xsearch\Model\Config;
use Magento\Elasticsearch\Elasticsearch5\Model\Client\Elasticsearch as Elasticsearch5;
use Magento\Elasticsearch6\Model\Client\Elasticsearch as Elasticsearch6;
use Magento\Elasticsearch7\Model\Client\Elasticsearch as Elasticsearch7;
use Magento\Framework\ObjectManagerInterface;

class Factory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ClientInterface|Elasticsearch5|Elasticsearch6|Elasticsearch7|null
     */
    private $client = null;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var GetAmastyElasticsearchClient
     */
    private $getAmastyElasticsearchClient;

    /**
     * @var array
     */
    private $clientPool;

    public function __construct(
        ObjectManagerInterface $objectManager,
        Config $config,
        GetAmastyElasticsearchClient $getAmastyElasticsearchClient,
        array $clientPool = []
    ) {
        $this->objectManager = $objectManager;
        $this->config = $config;
        $this->getAmastyElasticsearchClient = $getAmastyElasticsearchClient;
        $this->clientPool = $clientPool;
    }

    /**
     * @return ClientInterface|Elasticsearch6|Elasticsearch7|Elasticsearch5|mixed|null
     */
    public function getClient()
    {
        if (!$this->client) {
            $this->resolveClient();
        }
        return $this->client;
    }

    private function resolveClient(): void
    {
        if ($client = $this->getAmastyElasticsearchClient->execute()) {
            $this->client = $client;
        } else {
            $engine = $this->config->getEngine();
            $options = $this->config->getConnectionData();
            if (isset($this->clientPool[$engine])) {
                $this->client = $this->objectManager->create($this->clientPool[$engine], ['options' => $options]);
            }
        }
    }
}
