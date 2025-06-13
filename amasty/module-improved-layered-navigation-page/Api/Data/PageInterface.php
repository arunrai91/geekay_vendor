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
     * @return int
     */
    public function getPageId();

    /**
     * @return string
     */
    public function getPosition();

    /**
     * @return string
     */
    public function getUrl();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return string
     */
    public function getMetaTitle();

    /**
     * @return string
     */
    public function getMetaKeywords();

    /**
     * @return string
     */
    public function getMetaDescription();

    /**
     * @return string[][]
     */
    public function getConditions();

    /**
     * @return string[]
     */
    public function getCategories();

    /**
     * @return string[]
     */
    public function getStores();

    /**
     * @return mixed
     */
    public function getTopBlockId();

    /**
     * @return mixed
     */
    public function getBottomBlockId();

    /**
     * @param int $fileId
     * @return string
     */
    public function uploadImage($fileId);

    /**
     * @return void
     */
    public function removeImage();

    /**
     * @return string
     */
    public function getImage();

    /**
     * @return string|null
     */
    public function getTagRobots(): ?string;

    /**
     * @param int
     * @return PageInterface
     */
    public function setPageId($pageId);

    /**
     * @param string
     * @return PageInterface
     */
    public function setPosition($position);

    /**
     * @param string
     * @return PageInterface
     */
    public function setUrl($url);

    /**
     * @param string
     * @return PageInterface
     */
    public function setTitle($title);

    /**
     * @param string
     * @return PageInterface
     */
    public function setDescription($description);

    /**
     * @param string
     * @return PageInterface
     */
    public function setMetaTitle($metaTitle);

    /**
     * @param string
     * @return PageInterface
     */
    public function setMetaKeywords($metaKeywords);

    /**
     * @param string
     * @return PageInterface
     */
    public function setMetaDescription($metaDescription);

    /**
     * @param string[][]
     * @return PageInterface
     */
    public function setConditions($conditions);

    /**
     * @param string[]
     * @return PageInterface
     */
    public function setCategories($categories);

    /**
     * @param string[]
     * @return PageInterface
     */
    public function setStores($stores);

    /**
     * @param mixed
     * @return PageInterface
     */
    public function setTopBlockId($topBlockId);

    /**
     * @param mixed
     * @return PageInterface
     */
    public function setBottomBlockId($bottomBlockId);

    /**
     * @param string $image
     * @return PageInterface
     */
    public function setImage($image);

    /**
     * @param string $tagRobots
     * @return PageInterface
     */
    public function setTagRobots(string $tagRobots): PageInterface;

    /**
     * @return mixed
     */
    public function getData($key);
}
