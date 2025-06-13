<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Search\GetRequestQuery\ServicePreprocessor;

interface PreprocessorInterface
{
    /**
     * @param string $query
     * @return string
     */
    public function process($query);
}
