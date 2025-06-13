<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Api\Group\ByPosition;

use Amasty\Mostviewed\Api\Data\GroupProductsResultInterface;
use Amasty\Mostviewed\Api\Data\GroupProductsResultInterfaceFactory;
use Amasty\Mostviewed\Api\GroupRepositoryInterface;
use Amasty\Mostviewed\Model\Api\Group\Product\GetProductIds;
use Amasty\Mostviewed\Model\ConfigProvider;
use Amasty\Mostviewed\Model\ProductProvider;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;

class ResolveGroupProductsResult
{
    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var ProductProvider
     */
    private $productProvider;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var GroupProductsResultInterfaceFactory
     */
    private $groupProductsResultFactory;

    /**
     * @var GetProductIds
     */
    private $getProductIds;

    public function __construct(
        GroupRepositoryInterface $groupRepository,
        ProductProvider $productProvider,
        ConfigProvider $configProvider,
        GroupProductsResultInterfaceFactory $groupProductsResultFactory,
        GetProductIds $getProductIds
    ) {
        $this->groupRepository = $groupRepository;
        $this->productProvider = $productProvider;
        $this->configProvider = $configProvider;
        $this->groupProductsResultFactory = $groupProductsResultFactory;
        $this->getProductIds = $getProductIds;
    }

    /**
     * @param ProductInterface|CategoryInterface $entity
     * @param string $position
     * @param int|null $productId
     */
    public function execute($entity, string $position, ?int $productId = null): GroupProductsResultInterface
    {
        $shift = 0;
        while (true) {
            $group = $this->groupRepository->getGroupByIdAndPosition((int)$entity->getId(), $position, $shift);
            if (!$group) {
                break;
            }
            $productCollection = $this->productProvider->getAppliedProducts($group, $entity);
            if ($productCollection) {
                $productCollection->setPageSize($group->getMaxProducts());
                $this->productProvider->prepareCollection($group, $productCollection, $productId);
            }

            if ($productCollection && $productCollection->getSize()) {
                break;
            } elseif ($this->configProvider->isEnabledSubsequentRules()) {
                $shift++;
            }
        }

        /** @var GroupProductsResultInterface $groupProductsResult */
        $groupProductsResult = $this->groupProductsResultFactory->create();
        if ($group) {
            $groupProductsResult->setGroupId((int)$group->getGroupId());
            $groupProductsResult->setTitle($group->getBlockTitle());
            $groupProductsResult->setAddToCartButton((bool)$group->getAddToCart());
            $groupProductsResult->setWishlistButton($group->getDisplayWishlistButton());
            $groupProductsResult->setCompareButton($group->getDisplayCompareButton());
            $groupProductsResult->setBlockLayout((int)$group->getBlockLayout());
            if ($productCollection) {
                $groupProductsResult->setProductIds($this->getProductIds->execute($productCollection));
            }
        }

        return $groupProductsResult;
    }
}
