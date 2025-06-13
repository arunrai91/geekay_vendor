<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Api\Data;

interface SearchResultInterface
{
    /**
     * @return \Amasty\Xsearch\Api\Data\ProductRenderInterface[]
     */
    public function getProducts(): array;

    /**
     * @param \Amasty\Xsearch\Api\Data\ProductRenderInterface[] $products
     * @return void
     */
    public function setProducts(array $products): void;

    /**
     * @return int
     */
    public function getProductTotalCount(): int;

    /**
     * @param int $productTotalCount
     * @return void
     */
    public function setProductTotalCount(int $productTotalCount): void;

    /**
     * @return int
     */
    public function getProductLastPage(): int;

    /**
     * @param int $productLastPage
     * @return void
     */
    public function setProductLastPage(int $productLastPage): void;
}
