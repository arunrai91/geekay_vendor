<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Api\Data\Indexer\Structure;

interface EntityBuilderInterface
{
    /**
     * @return array
     */
    public function buildEntityFields();
}
