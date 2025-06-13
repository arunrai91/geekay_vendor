<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Api\Group\CustomPosition;

use Amasty\Mostviewed\Api\Data\GroupProductsResultInterface;
use Amasty\Mostviewed\Api\Data\GroupProductsResultInterfaceFactory;
use Amasty\Mostviewed\Api\GroupRepositoryInterface;
use Amasty\Mostviewed\Model\Api\Group\Product\GetProductIds;
use Amasty\Mostviewed\Model\OptionSource\BlockPosition;
use Amasty\Mostviewed\Model\ProductProvider;

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
        GroupProductsResultInterfaceFactory $groupProductsResultFactory,
        GetProductIds $getProductIds
    ) {
        $this->groupRepository = $groupRepository;
        $this->productProvider = $productProvider;
        $this->groupProductsResultFactory = $groupProductsResultFactory;
        $this->getProductIds = $getProductIds;
    }

    /**
     * @param int $groupId
     * @param int|null $productId
     * @return GroupProductsResultInterface
     */
    public function execute(int $groupId, ?int $productId = null): GroupProductsResultInterface
    {
        /** @var GroupProductsResultInterface $groupProductsResult */
        $groupProductsResult = $this->groupProductsResultFactory->create();

        $group = $this->groupRepository->getById($groupId);
        $group = $this->groupRepository->validateGroup($group);
        if (!$group || $group->getBlockPosition() !== BlockPosition::CUSTOM) {
            return $groupProductsResult;
        }

        $productCollection = $this->productProvider->getAppliedProducts($group, null);
        if ($productCollection) {
            $productCollection->setPageSize($group->getMaxProducts());
            $this->productProvider->prepareCollection($group, $productCollection, $productId);
        }

        $groupProductsResult->setGroupId((int)$group->getGroupId());
        $groupProductsResult->setTitle($group->getBlockTitle());
        $groupProductsResult->setAddToCartButton((bool)$group->getAddToCart());
        $groupProductsResult->setWishlistButton($group->getDisplayWishlistButton());
        $groupProductsResult->setCompareButton($group->getDisplayCompareButton());
        $groupProductsResult->setBlockLayout((int)$group->getBlockLayout());
        if ($productCollection) {
            $groupProductsResult->setProductIds($this->getProductIds->execute($productCollection));
        }

        return $groupProductsResult;
    }
}
