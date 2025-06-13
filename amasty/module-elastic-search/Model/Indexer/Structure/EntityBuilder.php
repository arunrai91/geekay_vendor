<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Indexer\Structure;

use Amasty\ElasticSearch\Api\Data\Indexer\Structure\EntityBuilderInterface;

class EntityBuilder
{

    /**
     * @var EntityBuilderInterface[]
     */
    private $entityBuilders;

    public function __construct(
        array $entityBuilders = []
    ) {
        $this->entityBuilders = $entityBuilders;
    }

    /**
     * @param string $indexerId
     * @return array
     */
    public function build($indexerId)
    {
        $fieldArray = [];
        if (isset($this->entityBuilders[$indexerId]) && is_array($this->entityBuilders[$indexerId])) {
            foreach ($this->entityBuilders[$indexerId] as $builder) {
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge.ForeachArrayMerge
                $fieldArray = array_merge($fieldArray, $builder->buildEntityFields());
            }
        }
        return $fieldArray;
    }
}
