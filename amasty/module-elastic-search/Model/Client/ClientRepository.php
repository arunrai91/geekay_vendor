<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Client;

use Amasty\ElasticSearch\Model\Config;
use Magento\Framework\Serialize\Serializer\Json as Serializer;

class ClientRepository implements ClientRepositoryInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @var ClientInterface[] ['key' => client, ...]
     */
    private $clients = [];

    public function __construct(
        Config $config,
        Serializer $serializer,
        ClientFactory $clientFactory
    ) {
        $this->config = $config;
        $this->serializer = $serializer;
        $this->clientFactory = $clientFactory;
    }

    public function get(?string $searchEngine = null, ?array $options = null): ClientInterface
    {
        $searchEngine = $searchEngine ?? $this->config->getEngine();
        $options = $options ?? $this->config->prepareConnectionData($options ?? []);

        $cacheKey = $this->getCacheKey($searchEngine, $options);
        if (!isset($this->clients[$cacheKey])) {
            $this->clients[$cacheKey] = $this->clientFactory->create($searchEngine, $options);
        }

        return $this->clients[$cacheKey];
    }

    private function getCacheKey(string $searchEngine, array $options): string
    {
        $options['engine'] = $searchEngine;
        $serializeData = $this->serializer->serialize(ksort($options));
        return sha1($serializeData);
    }
}
