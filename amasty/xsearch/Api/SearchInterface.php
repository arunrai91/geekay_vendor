<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Api;

/**
 * @api
 */
interface SearchInterface
{
    /**
     * @param string $query
     * @param int $storeId
     * @param string $currencyCode
     * @param int|null $page
     * @param int|null $pageSize
     * @return \Amasty\Xsearch\Api\Data\SearchResultInterface
     */
    public function search(
        string $query,
        int $storeId,
        string $currencyCode,
        ?int $page = null,
        ?int $pageSize = null
    ): \Amasty\Xsearch\Api\Data\SearchResultInterface;
}
