<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Api\Group\CustomPosition;

use Amasty\Mostviewed\Api\Data\GroupProductsResultInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class GetGroupForCategory
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ResolveGroupProductsResult
     */
    private $resolveGroupProductsResult;

    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager,
        ResolveGroupProductsResult $resolveGroupProductsResult
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->resolveGroupProductsResult = $resolveGroupProductsResult;
    }

    public function execute(int $groupId, int $categoryId): GroupProductsResultInterface
    {
        $category = $this->categoryRepository->get($categoryId, $this->storeManager->getStore()->getId());
        return $this->resolveGroupProductsResult->execute($groupId, $category);
    }
}
