<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Search\GetRequestQuery;

use Magento\Framework\Search\Request\QueryInterface;

interface InjectSubqueryInterface
{
    /**
     * @param QueryInterface $request
     * @param array $elasticQuery
     * @param string $conditionType
     * @return array
     */
    public function execute(array $elasticQuery, QueryInterface $request, $conditionType);
}
