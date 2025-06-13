<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Client;

use Amasty\ElasticSearch\Exception\NotAmastySearchEngine;
use Magento\Framework\ObjectManagerInterface;

class ClientFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $engines;

    /**
     * @var ConfigBuilder
     */
    private $configBuilder;

    public function __construct(
        ConfigBuilder $configBuilder,
        ObjectManagerInterface $objectManager,
        array $engines = []
    ) {
        $this->configBuilder = $configBuilder;
        $this->objectManager = $objectManager;
        $this->engines = $engines;
    }

    /**
     * @throws NotAmastySearchEngine
     */
    public function create(string $searchEngine, array $options): ClientInterface
    {
        if (!isset($this->engines[$searchEngine])) {
            throw new NotAmastySearchEngine(__('Selected engine not supported by Amasty_Elasticsearch module.'));
        }

        /** @var ClientInterface $client */
        $client = $this->objectManager->create($this->engines[$searchEngine], [
            'options' => $this->configBuilder->build($searchEngine, $options)
        ]);

        return $client;
    }
}
