<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\Search;

use Amasty\Xsearch\Model\Config;

class GetRequestQuery
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function executeExternalByFulltext(
        string $term,
        int $storeId,
        string $fulltextField,
        string $indexerId
    ): array {
        $filterQuery = ['must' => [['query_string' => [
            'default_field' => $fulltextField,
            'query' => $term
        ]]]];

        return [
            'index' => $this->config->getIndexName($indexerId, $storeId),
            'type'  => 'document',
            'body'  => [
                'from'      => 0,
                'size'      => 10000,
                '_source'   => [],
                'query'     => ['bool' => $filterQuery]
            ]
        ];
    }
}
