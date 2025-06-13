<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Api\Data;

use Magento\Framework\Api\SearchCriteriaInterface as SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\NoSuchEntityException;

interface FilterSettingRepositoryInterface
{
    public const TABLE = 'amasty_amshopby_filter_setting';

    /**
     * @param string $code
     * @param string|null $idFieldName
     * @return \Amasty\ShopbyBase\Api\Data\FilterSettingInterface
     * @throws NoSuchEntityException
     */
    public function get($code, $idFieldName = null);

    /**
     * @param string $attributeCode
     * @return \Amasty\ShopbyBase\Api\Data\FilterSettingInterface
     */
    public function getFilterSetting(string $attributeCode): FilterSettingInterface;

    /**
     * @param string $attributeCode
     * @return \Amasty\ShopbyBase\Api\Data\FilterSettingInterface|null
     */
    public function getByAttributeCode(string $attributeCode): ?FilterSettingInterface;

    /**
     * @param \Amasty\ShopbyBase\Api\Data\FilterSettingInterface $filterSetting
     * @return \Amasty\ShopbyBase\Api\Data\FilterSettingInterface
     */
    public function save(FilterSettingInterface $filterSetting): FilterSettingInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Amasty\ShopbyBase\Api\Data\FilterSettingSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;

    /**
     * @param string $attributeCode
     * @return void
     */
    public function deleteByAttributeCode(string $attributeCode): void;

    /**
     * @param \Amasty\ShopbyBase\Api\Data\FilterSettingInterface $filterSetting
     * @return \Amasty\ShopbyBase\Api\Data\FilterSettingInterface
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function update(FilterSettingInterface $filterSetting): FilterSettingInterface;
}
