<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\FilterSetting;

use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Api\Data\FilterSettingRepositoryInterface;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class FilterResolver
{
    /**
     * @var FilterSettingRepositoryInterface
     */
    private $filterSettingRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        FilterSettingRepositoryInterface $filterSettingRepository,
        LoggerInterface $logger,
        ?Config $eavConfig // @deprecated
    ) {
        $this->filterSettingRepository = $filterSettingRepository;
        $this->logger = $logger;
    }

    /**
     * Get FilterSettings by attribute
     */
    public function resolveByAttribute(AttributeInterface $entity): ?FilterSettingInterface
    {
        $extensionAttributes = $entity->getExtensionAttributes();
        $filterSetting = $extensionAttributes->getFilterSetting();
        if (!$filterSetting) {
            try {
                $filterSetting = $this->filterSettingRepository->loadByAttributeCode($entity->getAttributeCode());
                $filterSetting->setAttributeId((int)$entity->getAttributeId());
                $extensionAttributes->setFilterSetting($filterSetting);
                $entity->setExtensionAttributes($extensionAttributes);
            } catch (LocalizedException $e) {
                $this->logger->critical($e);
            }
        }

        return $filterSetting;
    }

    /**
     * @deprecated no need to proxy methods
     * @see \Amasty\ShopbyBase\Model\FilterSettingRepository::getFilterSetting with exception on empty
     * @see \Amasty\ShopbyBase\Model\FilterSettingRepository::loadByAttributeCode without exception on empty
     */
    public function getFilterSettingByCode(?string $code): ?FilterSettingInterface
    {
        return  $this->filterSettingRepository->loadByAttributeCode($code);
    }

    public function resolveByFilter(FilterInterface $layerFilter): ?FilterSettingInterface
    {
        $attributeCode = $this->resolveCodeByFilter($layerFilter);
        if (!$attributeCode) {
            return null;
        }
        $setting = $this->filterSettingRepository->loadByAttributeCode($attributeCode);

        if ($setting !== null) {
            $setting->setAttributeModel($layerFilter->getData('attribute_model'));
        }

        return $setting;
    }

    public function resolveCodeByFilter(FilterInterface $layerFilter): ?string
    {
        if ($layerFilter instanceof \Amasty\ShopbyBase\Model\CustomFilterInterface) {
            return $layerFilter->getFilterCode();
        }
        if ($layerFilter instanceof \Magento\Catalog\Model\Layer\Filter\FilterInterface) {
            return $layerFilter->getAttributeModel()->getAttributeCode();
        }

        $attributeModel = $layerFilter->getData('attribute_model');

        return is_object($attributeModel) ? $attributeModel->getAttributeCode() : null;
    }

    /**
     * Load filter sitting for array of settings.
     *
     * Bunch loading faster than single load in cycle.
     *
     * @param AbstractFilter[] $filters
     */
    public function preloadFiltersSettings(array $filters): void
    {
        $filterCodes = [];
        foreach ($filters as $filter) {
            $code = $this->resolveCodeByFilter($filter);
            if ($code !== null) {
                $filterCodes[] = $code;
            }
        }

        $this->filterSettingRepository->loadFiltersSettings($filterCodes);
    }

    /**
     * @deprecated renamed to resolveByAttribute
     * @see resolveByAttribute
     */
    public function getFilterSetting(AttributeInterface $entity): ?FilterSettingInterface
    {
        return $this->resolveByAttribute($entity);
    }
}
