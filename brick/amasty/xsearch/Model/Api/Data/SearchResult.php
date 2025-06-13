<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\Api\Data;

use Amasty\Xsearch\Api\Data\ProductRenderInterface;
use Amasty\Xsearch\Api\Data\SearchResultInterface;

class SearchResult implements SearchResultInterface
{
    /**
     * @var ProductRenderInterface[]
     */
    private $products = [];

    /**
     * @var int
     */
    private $productTotalCount = 0;

    /**
     * @var int
     */
    private $productLastPage = 0;

    /**
     * @return ProductRenderInterface[]
     */
    public function getProducts(): array
    {
        return $this->products;
    }

    /**
     * @param ProductRenderInterface[] $products
     * @return void
     */
    public function setProducts(array $products): void
    {
        $this->products = $products;
    }

    public function getProductTotalCount(): int
    {
        return $this->productTotalCount;
    }

    public function setProductTotalCount(int $productTotalCount): void
    {
        $this->productTotalCount = $productTotalCount;
    }

    public function getProductLastPage(): int
    {
        return $this->productLastPage;
    }

    public function setProductLastPage(int $productLastPage): void
    {
        $this->productLastPage = $productLastPage;
    }
}
