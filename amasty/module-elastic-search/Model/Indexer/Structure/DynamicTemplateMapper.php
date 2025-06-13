<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Indexer\Structure;

class DynamicTemplateMapper
{
    /**
     * @var array
     */
    private $mappers = [];

    public function __construct(array $mappers = [])
    {
        foreach ($mappers as $key => $mapper) {
            if (method_exists($mapper, 'map')) {
                $this->mappers[$key] = $mapper;
            }
        }
    }

    /**
     * @param int $storeId
     * @return array
     */
    public function map($storeId)
    {
        $mappedData = [];
        foreach ($this->mappers as $key => $mapper) {
            $mappedData[$key] = $mapper->map($storeId);
        }

        return $mappedData;
    }
}
