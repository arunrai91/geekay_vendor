<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Plugin\Catalog\Model\Layer\FilterableAttributeListInterface;

use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Api\Data\FilterSettingRepositoryInterface;
use Magento\Catalog\Model\Layer\FilterableAttributeListInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class LoadExtensionAttributes
{
    /**
     * @var FilterSettingRepositoryInterface
     */
    private $filterSettingRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    public function __construct(
        FilterSettingRepositoryInterface $filterSettingRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->filterSettingRepository = $filterSettingRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param FilterableAttributeListInterface $subject
     * @param Collection $collection
     * @return Collection
     */
    public function afterGetList(
        FilterableAttributeListInterface $subject,
        Collection $collection
    ): Collection {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            FilterSettingInterface::ATTRIBUTE_CODE,
            $collection->getColumnValues(FilterSettingInterface::ATTRIBUTE_CODE),
            'in'
        );

        //Use function getList for load all items in one query
        $this->filterSettingRepository->getList($searchCriteria->create());
        foreach ($collection as $entity) {
            $filterSetting = $this->filterSettingRepository->getByAttributeCode(
                $entity->getData(FilterSettingInterface::ATTRIBUTE_CODE)
            );
            if ($filterSetting) {
                $this->setExtensionAttribute($entity, $filterSetting);
            }

        }

        return $collection;
    }

    private function setExtensionAttribute(AttributeInterface $entity, FilterSettingInterface $filterSetting): void
    {
        $extensionAttributes = $entity->getExtensionAttributes();
        $extensionAttributes->setFilterSetting($filterSetting);
        $entity->setExtensionAttributes($extensionAttributes);
    }
}
