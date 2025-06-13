<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Api\Data;

interface GroupProductsResultInterface
{
    /**
     * @return int
     */
    public function getGroupId(): ?int;

    /**
     * @param int $groupId
     * @return void
     */
    public function setGroupId(int $groupId): void;

    /**
     * @return string
     */
    public function getTitle(): ?string;

    /**
     * @param string $title
     * @return void
     */
    public function setTitle(string $title): void;

    /**
     * @return bool
     */
    public function isAddToCartButtonShow(): ?bool;

    /**
     * @param bool $isAddToCartButtonShowed
     * @return void
     */
    public function setAddToCartButton(bool $isAddToCartButtonShowed): void;

    /**
     * @return bool
     */
    public function isWishlistButtonShow(): ?bool;

    /**
     * @param bool $isWishlistButtonShowed
     * @return void
     */
    public function setWishlistButton(bool $isWishlistButtonShowed): void;

    /**
     * @return bool
     */
    public function isCompareButtonShow(): ?bool;

    /**
     * @param bool $isCompareButtonShowed
     * @return void
     */
    public function setCompareButton(bool $isCompareButtonShowed): void;

    /**
     * @return int
     */
    public function getBlockLayout(): ?int;

    /**
     * @param int $blockLayout
     * @return void
     */
    public function setBlockLayout(int $blockLayout): void;

    /**
     * @return int[]
     */
    public function getProductIds(): array;

    /**
     * @param int[] $productIds
     * @return void
     */
    public function setProductIds(array $productIds): void;
}
