<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Indexer;

use Amasty\ElasticSearch\Model\Adapter\AdapterInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Indexer\IndexStructureInterface;

class StructureHandler implements IndexStructureInterface
{
    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var AdapterInterface
     */
    private $adapter;

    public function __construct(
        ScopeResolverInterface $scopeResolver,
        AdapterInterface $adapter
    ) {
        $this->scopeResolver = $scopeResolver;
        $this->adapter = $adapter;
    }

    public function delete(
        $indexerId,
        array $dimensions = []
    ) {
        $dimension = current($dimensions);
        $scopeId = (int)$this->scopeResolver->getScope($dimension->getValue())->getId();
        $this->adapter->cleanIndex($scopeId, $indexerId);
    }

    public function create(
        $indexerId,
        array $fields,
        array $dimensions = []
    ) {
        $dimension = current($dimensions);
        $scopeId = (int)$this->scopeResolver->getScope($dimension->getValue())->getId();
        $this->adapter->checkIndex($scopeId, $indexerId);
    }
}
