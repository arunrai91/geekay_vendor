<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Api\Data;

use Amasty\Mostviewed\Api\Data\GroupProductsResultInterface;

class GroupProductsResult implements GroupProductsResultInterface
{
    /**
     * @var int|null
     */
    private $groupId;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var bool|null
     */
    private $isAddToCartButtonShowed;

    /**
     * @var bool|null
     */
    private $isWishlistButtonShowed;

    /**
     * @var bool|null
     */
    private $isCompareButtonShowed;

    /**
     * @var int|null
     */
    private $blockLayout;

    /**
     * @var int[]
     */
    private $productIds = [];

    public function getGroupId(): ?int
    {
        return $this->groupId;
    }

    public function setGroupId(int $groupId): void
    {
        $this->groupId = $groupId;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function isAddToCartButtonShow(): ?bool
    {
        return $this->isAddToCartButtonShowed;
    }

    public function setAddToCartButton(bool $isAddToCartButtonShowed): void
    {
        $this->isAddToCartButtonShowed = $isAddToCartButtonShowed;
    }

    public function isWishlistButtonShow(): ?bool
    {
        return $this->isWishlistButtonShowed;
    }

    public function setWishlistButton(bool $isWishlistButtonShowed): void
    {
        $this->isWishlistButtonShowed = $isWishlistButtonShowed;
    }

    public function isCompareButtonShow(): ?bool
    {
        return $this->isCompareButtonShowed;
    }

    public function setCompareButton(bool $isCompareButtonShowed): void
    {
        $this->isCompareButtonShowed = $isCompareButtonShowed;
    }

    public function getBlockLayout(): ?int
    {
        return $this->blockLayout;
    }

    public function setBlockLayout(int $blockLayout): void
    {
        $this->blockLayout = $blockLayout;
    }

    /**
     * @return int[]
     */
    public function getProductIds(): array
    {
        return $this->productIds;
    }

    /**
     * @param int[] $productIds
     */
    public function setProductIds(array $productIds): void
    {
        $this->productIds = $productIds;
    }
}
