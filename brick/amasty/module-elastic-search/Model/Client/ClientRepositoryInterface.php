<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Client;

/**
 * Interface ClientRepositoryInterface
 */
interface ClientRepositoryInterface
{
    /**
     * @param string|null $searchEngine
     * @param array|null $options
     * @return \Amasty\ElasticSearch\Model\Client\ClientInterface
     */
    public function get(?string $searchEngine = null, ?array $options = null): ClientInterface;
}
