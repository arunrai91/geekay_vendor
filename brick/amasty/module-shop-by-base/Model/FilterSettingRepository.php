<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model;

use Amasty\ShopbyBase\Api\Data\FilterSettingRepositoryInterface;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Api\Data\FilterSettingSearchResultsInterface;
use Amasty\ShopbyBase\Model\ResourceModel\FilterSetting as FilterSettingResource;
use Amasty\ShopbyBase\Model\ResourceModel\FilterSetting\Collection;
use Amasty\ShopbyBase\Model\ResourceModel\FilterSetting\CollectionFactory;
use Amasty\ShopbyBase\Model\ResourceModel\IsProductAttributeExist;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface as SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class FilterSettingRepository implements FilterSettingRepositoryInterface
{
    public const API_FIELD_ID = 'id';

    /**
     * @var FilterSettingResource
     */
    private $resource;

    /**
     * @var FilterSettingFactory
     */
    private $factory;

    /**
     * @var FilterSetting[]
     */
    private $items = [];

    /**
     * @var FilterSettingProxyFactory
     */
    private $proxyFactory;

    /**
     * @var SearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var IsProductAttributeExist
     */
    private $isProductAttributeExist;

    /**
     * @var FilterDataLoader
     */
    private $filterDataLoader;

    public function __construct(
        FilterSettingResource $resource,
        FilterSettingFactory $factory,
        CollectionFactory $collectionFactory,
        FilterSettingProxyFactory $proxyFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        IsProductAttributeExist $isProductAttributeExist,
        FilterDataLoader $filterDataLoader
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
        $this->proxyFactory = $proxyFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->isProductAttributeExist = $isProductAttributeExist;
        $this->filterDataLoader = $filterDataLoader;
    }

    /**
     * @deprecated 2.15.0
     * @param string $code
     * @param string|null $idFieldName
     * @return FilterSettingInterface
     * @throws NoSuchEntityException
     */
    public function get($code, $idFieldName = null)
    {
        if (!isset($this->items[$code])) {
            $this->load($code, $idFieldName);
        }

        return $this->items[$code];
    }

    public function getFilterSetting(string $attributeCode): FilterSettingInterface
    {
        $filterSetting = $this->loadByAttributeCode($attributeCode);
        if ($filterSetting === null || !$filterSetting->getId()) {
            throw new NoSuchEntityException(__('Requested entity doesn\'t exist'));
        } else {
            return $filterSetting;
        }
    }

    /**
     * @deprecated Use FilterSettingRepository::getFilterSetting
     * @param string|null $attributeCode
     * @return FilterSettingInterface|null
     */
    public function getByAttributeCode(?string $attributeCode): ?FilterSettingInterface
    {
        if (!isset($this->items[$attributeCode])) {
            return $this->proxyFactory->create(['attributeCode' => $attributeCode]);
        }

        return $this->items[$attributeCode];
    }

    public function loadByAttributeCode(string $attributeCode)
    {
        if (!isset($this->items[$attributeCode])) {
            $this->load($attributeCode, FilterSettingInterface::ATTRIBUTE_CODE);
        }

        return $this->items[$attributeCode];
    }

    private function load(string $attributeCode, $idFieldName = null): void
    {
        $entity = $this->factory->create();
        $entity->setAttributeCode($attributeCode);
        $this->filterDataLoader->load($entity, $attributeCode, $idFieldName);
        $this->items[$attributeCode] = $entity;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return FilterSettingSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        $collection = $this->collectionFactory->create();
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }

        $searchResults->setTotalCount($collection->getSize());
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        $filterSettings = [];
        foreach ($collection->getItems() as $item) {
            $this->items[$item->getAttributeCode()] = $item;
            $filterSettings[] = $item;
        }
        $searchResults->setItems($filterSettings);

        return $searchResults;
    }

    public function loadFiltersSettings(array $attributeCodes): array
    {
        foreach ($attributeCodes as $key => $attributeCode) {
            if (isset($this->items[$attributeCode])) {
                unset($attributeCodes[$key]);
            }
        }
        if (empty($attributeCodes)) {
            return [];
        }
        $filterSettings = [];
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(FilterSettingInterface::ATTRIBUTE_CODE, ['in' => $attributeCodes]);
        foreach ($collection->getItems() as $item) {
            $item->afterLoad();
            $attributeCode = $item->getAttributeCode();
            $this->items[$attributeCode] = $item;
            $filterSettings[$attributeCode] = $item;
        }

        if (count($filterSettings) === count($attributeCodes)) {
            return $filterSettings;
        }

        $emptyItem = $collection->getNewEmptyItem();
        // set not loaded items to prevent empty loading by single item
        foreach ($attributeCodes as $attributeCode) {
            if (!isset($this->items[$attributeCode])) {
                $item = clone $emptyItem;
                $item->setAttributeCode($attributeCode);
                $this->filterDataLoader->load($item, $attributeCode);
                $this->items[$attributeCode] = $item;
            }
        }

        return $filterSettings;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $itemCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $collection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * @param FilterSettingInterface $filterSetting
     * @return FilterSettingInterface
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function update(FilterSettingInterface $filterSetting): FilterSettingInterface
    {
        $model = $this->get((string) $filterSetting->getId(), FilterSettingInterface::FILTER_SETTING_ID);
        if (!$model->isObjectNew()) {
            $this->save($filterSetting);
        } else {
            throw new NoSuchEntityException(__('Wrong parameter %1.', self::API_FIELD_ID));
        }

        return $filterSetting;
    }

    /**
     * @param FilterSettingInterface $filterSetting
     * @return FilterSettingInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(FilterSettingInterface $filterSetting): FilterSettingInterface
    {
        if ($this->isProductAttributeExist->execute($filterSetting->getAttributeCode())) {
            $this->resource->save(
                $filterSetting instanceof FilterSettingProxy
                    ? $filterSetting->getSubject()
                    : $filterSetting
            );
        } else {
            throw new NoSuchEntityException(__('Wrong parameter %1.', FilterSettingInterface::ATTRIBUTE_CODE));
        }

        return $filterSetting;
    }

    public function deleteByAttributeCode(string $attributeCode): void
    {
        $filterSetting = $this->loadByAttributeCode($attributeCode);
        if (!$filterSetting->getId()) {
            throw new NoSuchEntityException(__('Requested entity doesn\'t exist'));
        } else {
            $this->resource->delete($filterSetting);
        }
    }
}
