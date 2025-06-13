<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Search;

use Magento\Framework\Search\RequestInterface;

class GetCountQuery
{
    /**
     * @var GetRequestQuery
     */
    private $getRequestQuery;

    public function __construct(GetRequestQuery $getRequestQuery)
    {
        $this->getRequestQuery = $getRequestQuery;
    }

    public function execute(RequestInterface $request): array
    {
        $requestQuery = $this->getRequestQuery->execute($request);
        unset($requestQuery['track_total_hits']);
        unset($requestQuery['body']['sort']);
        unset($requestQuery['body']['from']);
        unset($requestQuery['body']['size']);
        unset($requestQuery['body']['stored_fields']);
        unset($requestQuery['body']['aggregations']);

        return $requestQuery;
    }
}
