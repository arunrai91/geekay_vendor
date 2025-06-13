<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Search;

use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Framework\ObjectManagerInterface;

class DataProviderFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $instanceName;

    public function __construct(
        ObjectManagerInterface $objectManager,
        $instanceName = DataProvider::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    public function create(
        string $indexerId = Fulltext::INDEXER_ID,
        ?string $aggregationFieldName = null
    ): DataProvider {
        return $this->objectManager->create(
            $this->instanceName,
            [
                'indexerId' => $indexerId,
                'aggregationFieldName' => $aggregationFieldName
            ]
        );
    }
}
