<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Catalog\Model\Category\Attribute\Backend;

use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\Category\Attribute\Backend\Image;
use Amasty\Shopby\Plugin\Catalog\Model\Category;

class ImagePlugin
{
    /**
     * @var array|null
     */
    private ?array $currentThumbnailFiles = null;

    /**
     * @param Image $subject
     * @param CategoryModel $category
     */
    public function beforeBeforeSave(Image $subject, $category): void
    {
        if ($subject->getAttribute()->getName() === Category::THUMBNAIL && is_array($category->getThumbnail())) {
            $this->currentThumbnailFiles = $category->getThumbnail();
        } else {
            $this->currentThumbnailFiles = null;
        }
    }

    /**
     * @param Image $subject
     * @param Image|null $result
     * @param CategoryModel $category
     * @return Image|null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterBeforeSave(Image $subject, $result, $category)
    {
        if (isset($this->currentThumbnailFiles[0]['name'])) {
            $category->setThumbnail($this->currentThumbnailFiles[0]['name']);
        }

        return $result;
    }
}
