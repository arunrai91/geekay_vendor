<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Client;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\RuntimeException;

class ConfigBuilder
{
    /**
     * @var ConfigBuilderInterface[] ['engine' => builder, ...]
     */
    private $configBuilders;

    public function __construct(array $configBuilders = [])
    {
        $this->configBuilders = $configBuilders;
    }

    /**
     * @throws LocalizedException
     */
    public function build(string $engine, array $clientOptions): array
    {
        $configBuilder = $this->configBuilders[$engine] ?? $this->configBuilders['default'] ?? null;
        if ($configBuilder === null) {
            throw new RuntimeException(__('Config builder not set.'));
        }

        return $configBuilder->build($clientOptions);
    }
}
