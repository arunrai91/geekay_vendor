<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Block\Search;

use Amasty\Xsearch\Model\Search\Category\UrlDataProvider;
use Magento\Framework\DataObject;

class Category extends AbstractSearch
{
    public const CATEGORY_BLOCK_TYPE = 'category';

    /**
     * @var array
     */
    private $categoryData = [];

    /**
     * @return string
     */
    public function getBlockType()
    {
        return self::CATEGORY_BLOCK_TYPE;
    }

    /**
     * @inheritdoc
     */
    protected function generateCollection()
    {
        $rootId = $this->_storeManager->getStore()->getRootCategoryId();
        $storeId = $this->_storeManager->getStore()->getId();
        $collection = parent::generateCollection()
            ->setStoreId($storeId)
            ->addNameToResult()
            ->addIsActiveFilter()
            ->addAttributeToSelect('description')
            ->addFieldToFilter('path', ['like' => '1/' . $rootId . '/%'])
            ->addSearchFilter($this->getQuery()->getQueryText())
            ->setPageSize($this->getLimit());

        if ($filteredCategoryIds = $this->configProvider->getExcludeCategoryIdsArray()) {
            $collection->addFieldToFilter('entity_id', ['nin' => $filteredCategoryIds]);
        }

        return $collection;
    }

    /**
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    public function getName(\Magento\Framework\DataObject $item): string
    {
        if ($this->configProvider->isDisplayFullCategoryPath()) {
            $result = $this->generateName($this->getItemTitle($item));
        } else {
            $result = parent::getName($item);
        }

        return $result;
    }

    public function formatAccordingToConstraint(string $text): string
    {
        if (!$this->configProvider->isDisplayFullCategoryPath()) {
            $text = parent::formatAccordingToConstraint($text);
        }

        return $text;
    }

    public function getItemData(DataObject $item): array
    {
        $data = parent::getItemData($item);
        $data['full_path'] = $this->getItemTitle($item);

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getItemTitle(\Magento\Framework\DataObject $item)
    {
        $path = array_reverse(explode(',', $item->getPathInStore()));
        $categoryTitle = '';
        $titles = $this->getCategoryData();
        foreach ($path as $id) {
            if (!empty($titles[$id])) {
                $categoryTitle .= $titles[$id]['name'];
                $categoryTitle .= ($id !== $item->getId()) ? ' > ' : '';
            }
        }

        return $categoryTitle ?: $item->getName();
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCategoryData()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        if (!isset($this->categoryData[$storeId])) {
            /** @var UrlDataProvider  $categoryUrlDataProvider*/
            $categoryUrlDataProvider = $this->getData('categoryUrlDataProvider');
            $categoryData = $categoryUrlDataProvider->getSearchPopupCategoryData();
            $this->categoryData[$storeId] = array_map(function ($category) {
                $category['url'] = $this->getRelativeLink($category['full_link']);

                return $category;
            }, $categoryData);
        }

        return $this->categoryData[$storeId];
    }

    public function getDescription(\Magento\Framework\DataObject $category): string
    {
        if (!$category->getDescription()) {
            return '';
        }

        $description = $this->removeStyleContent($category->getDescription());
        $descStripped = $this->stripTags($description, null, true);

        return $this->getHighlightText($descStripped);
    }

    /**
     * @param string $content
     * @return string
     * phpcs:disable Magento2.Functions.DiscouragedFunction
     */
    private function removeStyleContent(string $content): string
    {
        return preg_replace(
            '|<style[^>]*?>(.*?)</style>|si',
            '',
            html_entity_decode($content)
        );
    }

    /**
     * @param \Magento\Framework\DataObject $item
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSearchUrl(\Magento\Framework\DataObject $item)
    {
        $categoryData = $this->getCategoryData();

        return $categoryData[$item->getId()]['full_link'] ?? '';
    }
}
