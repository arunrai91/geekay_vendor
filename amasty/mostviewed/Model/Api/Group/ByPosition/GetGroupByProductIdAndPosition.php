<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Api\Group\ByPosition;

use Amasty\Mostviewed\Api\Data\GroupProductsResultInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class GetGroupByProductIdAndPosition
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ResolveGroupProductsResult
     */
    private $resolveGroupProductsResult;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        ResolveGroupProductsResult $resolveGroupProductsResult
    ) {
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->resolveGroupProductsResult = $resolveGroupProductsResult;
    }

    public function execute(int $productId, string $position): GroupProductsResultInterface
    {
        $product = $this->productRepository->getById($productId, false, $this->storeManager->getStore()->getId());
        return $this->resolveGroupProductsResult->execute($product, $position, $productId);
    }
}
