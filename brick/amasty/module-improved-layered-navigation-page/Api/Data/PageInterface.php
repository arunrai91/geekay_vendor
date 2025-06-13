<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Page for Magento 2 (System)
 */

namespace Amasty\ShopbyPage\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface PageInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the getters in snake case
     */
    public const PAGE_ID = 'page_id';
    public const POSITION = 'position';
    public const URL = 'url';
    public const TITLE = 'title';
    public const DESCRIPTION = 'description';
    public const META_TITLE = 'meta_title';
    public const META_KEYWORDS = 'meta_keywords';
    public const META_DESCRIPTION = 'meta_description';
    public const CONDITIONS = 'conditions';
    public const CATEGORIES = 'categories';
    public const TOP_BLOCK_ID = 'top_block_id';
    public const BOTTOM_BLOCK_ID = 'bottom_block_id';
    public const STORES = 'stores';
    public const IMAGE = 'image';
    public const TAG_ROBOTS = 'tag_robots';
    public const TABLE_NAME = 'amasty_amshopby_page';

    /**
     * @return int|null
     */
    public function getPageId(): ?int;

    /**
     * @return string|null
     */
    public function getPosition(): ?string;

    /**
     * @return string|null
     */
    public function getUrl(): ?string;

    /**
     * @return string|null
     */
    public function getTitle(): ?string;

    /**
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * @return string|null
     */
    public function getMetaTitle(): ?string;

    /**
     * @return string|null
     */
    public function getMetaKeywords(): ?string;

    /**
     * @return string|null
     */
    public function getMetaDescription(): ?string;

    /**
     * @return string[][]|null
     */
    public function getConditions(): ?array;

    /**
     * @return string[]|null
     */
    public function getCategories(): ?array;

    /**
     * @return string[]|null
     */
    public function getStores(): ?array;

    /**
     * @return int|null
     */
    public function getTopBlockId(): ?int;

    /**
     * @return int|null
     */
    public function getBottomBlockId(): ?int;

    /**
     * @return string|null
     */
    public function getImage(): ?string;

    /**
     * @return string|null
     */
    public function getTagRobots(): ?string;

    /**
     * @param int $pageId
     * @return void
     */
    public function setPageId(int $pageId): void;

    /**
     * @param string $position
     * @return void
     */
    public function setPosition(string $position): void;

    /**
     * @param string|null $url
     * @return void
     */
    public function setUrl(?string $url): void;

    /**
     * @param string|null $title
     * @return void
     */
    public function setTitle(?string $title): void;

    /**
     * @param string|null $description
     * @return void
     */
    public function setDescription(?string $description): void;

    /**
     * @param string|null $metaTitle
     * @return void
     */
    public function setMetaTitle(?string $metaTitle): void;

    /**
     * @param string|null $metaKeywords
     * @return void
     */
    public function setMetaKeywords(?string $metaKeywords): void;

    /**
     * @param string|null $metaDescription
     * @return void
     */
    public function setMetaDescription(?string $metaDescription): void;

    /**
     * @param string[][] $conditions
     * @return void
     */
    public function setConditions(?array $conditions): void;

    /**
     * @param string[]|null $categories
     * @return void
     */
    public function setCategories(?array $categories): void;

    /**
     * @param string[] $stores
     * @return void
     */
    public function setStores(array $stores): void;

    /**
     * @param int|null $topBlockId
     * @return void
     */
    public function setTopBlockId(?int $topBlockId): void;

    /**
     * @param int|null $bottomBlockId
     * @return void
     */
    public function setBottomBlockId(?int $bottomBlockId): void;

    /**
     * @param string|null $image
     * @return void
     */
    public function setImage(?string $image): void;

    /**
     * @param string $tagRobots
     * @return void
     */
    public function setTagRobots(string $tagRobots): void;
}
