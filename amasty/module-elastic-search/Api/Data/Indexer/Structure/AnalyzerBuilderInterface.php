<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Api\Data\Indexer\Structure;

interface AnalyzerBuilderInterface
{
    /**
     * @param int $storeId
     * @return array
     */
    public function build($storeId);
}
