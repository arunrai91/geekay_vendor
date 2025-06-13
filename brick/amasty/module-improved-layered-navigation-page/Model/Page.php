<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Page for Magento 2 (System)
 */

namespace Amasty\ShopbyPage\Model;

use Amasty\ShopbyPage\Api\Data\PageInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class Page extends AbstractExtensibleModel implements PageInterface
{
    /**
     * Position of placing meta data in category
     */
    public const POSITION_REPLACE = 'replace';
    public const POSITION_AFTER = 'after';
    public const POSITION_BEFORE = 'before';

    public const CATEGORY_FORCE_USE_CANONICAL = 'amshopby_page_force_use_canonical';
    public const MATCHED_PAGE = 'amshopby_matched_page';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Amasty\ShopbyPage\Model\ResourceModel\Page::class);
    }

    public function getPageId(): ?int
    {
        return $this->hasData(self::PAGE_ID)
            ? (int)$this->_getData(self::PAGE_ID)
            : null;
    }

    public function getPosition(): ?string
    {
        return $this->hasData(self::POSITION)
            ? (string)$this->_getData(self::POSITION)
            : null;
    }

    public function getUrl(): ?string
    {
        return $this->hasData(self::URL)
            ? (string)$this->_getData(self::URL)
            : null;
    }

    public function getTitle(): ?string
    {
        return $this->hasData(self::TITLE)
            ? (string)$this->_getData(self::TITLE)
            : null;
    }

    public function getDescription(): ?string
    {
        return $this->hasData(self::DESCRIPTION)
            ? (string)$this->_getData(self::DESCRIPTION)
            : null;
    }

    public function getMetaTitle(): ?string
    {
        return $this->hasData(self::META_TITLE)
            ? (string)$this->_getData(self::META_TITLE)
            : null;
    }

    public function getMetaKeywords(): ?string
    {
        return $this->hasData(self::META_KEYWORDS)
            ? (string)$this->_getData(self::META_KEYWORDS)
            : null;
    }

    public function getMetaDescription(): ?string
    {
        return $this->hasData(self::META_DESCRIPTION)
            ? (string)$this->_getData(self::META_DESCRIPTION)
            : null;
    }

    /**
     * @return string[][]|null
     */
    public function getConditions(): ?array
    {
        return $this->hasData(self::CONDITIONS)
            ? $this->_getData(self::CONDITIONS)
            : null;
    }

    /**
     * @return string[]|null
     */
    public function getCategories(): ?array
    {
        return $this->hasData(self::CATEGORIES)
            ? $this->_getData(self::CATEGORIES)
            : null;
    }

    /**
     * @return int[]|null
     */
    public function getStores(): ?array
    {
        return $this->hasData(self::STORES)
            ? $this->_getData(self::STORES)
            : null;
    }

    public function getImage(): ?string
    {
        return $this->hasData(self::IMAGE)
            ? $this->_getData(self::IMAGE)
            : null;
    }

    public function getTopBlockId(): ?int
    {
        return $this->_getData(self::TOP_BLOCK_ID)
            ? (int)$this->_getData(self::TOP_BLOCK_ID)
            : null;
    }

    public function getBottomBlockId(): ?int
    {
        return $this->_getData(self::BOTTOM_BLOCK_ID)
            ? (int)$this->_getData(self::BOTTOM_BLOCK_ID)
            : null;
    }

    public function getTagRobots(): ?string
    {
        return $this->hasData(self::TAG_ROBOTS)
            ? $this->_getData(self::TAG_ROBOTS)
            : null;
    }

    public function setPageId(int $pageId): void
    {
        $this->setData(self::PAGE_ID, $pageId);
    }

    public function setPosition(string $position): void
    {
        $this->setData(self::POSITION, $position);
    }

    public function setUrl(?string $url): void
    {
        $this->setData(self::URL, $url);
    }

    public function setTitle(?string $title): void
    {
        $this->setData(self::TITLE, $title);
    }

    public function setDescription(?string $description): void
    {
        $this->setData(self::DESCRIPTION, $description);
    }

    public function setMetaTitle(?string $metaTitle): void
    {
        $this->setData(self::META_TITLE, $metaTitle);
    }

    public function setMetaKeywords(?string $metaKeywords): void
    {
        $this->setData(self::META_KEYWORDS, $metaKeywords);
    }

    public function setMetaDescription(?string $metaDescription): void
    {
        $this->setData(self::META_DESCRIPTION, $metaDescription);
    }

    /**
     * @param string[][]|null $conditions
     */
    public function setConditions(?array $conditions): void
    {
        $this->setData(self::CONDITIONS, $conditions);
    }

    /**
     * @param string[]|null $categories
     */
    public function setCategories(?array $categories): void
    {
        $this->setData(self::CATEGORIES, $categories);
    }

    /**
     * @param int[] $stores
     */
    public function setStores(array $stores): void
    {
        $this->setData(self::STORES, $stores);
    }

    public function setTopBlockId(?int $topBlockId): void
    {
        $this->setData(self::TOP_BLOCK_ID, $topBlockId);
    }

    public function setBottomBlockId(?int $bottomBlockId): void
    {
        $this->setData(self::BOTTOM_BLOCK_ID, $bottomBlockId);
    }

    public function setImage(?string $image): void
    {
        $this->setData(self::IMAGE, $image);
    }

    public function setTagRobots(string $tagRobots): void
    {
        $this->setData(self::TAG_ROBOTS, $tagRobots);
    }
}
