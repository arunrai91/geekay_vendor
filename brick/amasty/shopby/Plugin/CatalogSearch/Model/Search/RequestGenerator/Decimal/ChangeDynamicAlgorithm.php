<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\CatalogSearch\Model\Search\RequestGenerator\Decimal;

use Amasty\Shopby\Model\Search\Dynamic\Custom;
use Amasty\Shopby\Model\Source\DisplayMode;
use Amasty\Shopby\Model\Source\RangeAlgorithm;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Api\Data\FilterSettingRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\CatalogSearch\Model\Search\RequestGenerator\Decimal as RequestGenerator;

class ChangeDynamicAlgorithm
{
    /**
     * @var FilterSettingRepositoryInterface
     */
    private $filterSettingRepository;

    public function __construct(FilterSettingRepositoryInterface $filterSettingRepository)
    {
        $this->filterSettingRepository = $filterSettingRepository;
    }

    /**
     * @see RequestGenerator::getAggregationData
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAggregationData(
        RequestGenerator $subject,
        array $aggregationData,
        Attribute $attribute
    ): array {
        if (isset($aggregationData['method'])
            && $attribute->getFrontendInput() === 'price'
            && $this->isCustomRangeAlgorithmEnabled($attribute)
        ) {
            $aggregationData['method'] = Custom::ALGORITHM_CODE;
        }

        return $aggregationData;
    }

    private function getFilterSetting(string $attributeCode): ?FilterSettingInterface
    {
        return $this->filterSettingRepository->getByAttributeCode($attributeCode);
    }

    /**
     * Check if the custom range algorithm is enabled for the attribute.
     */
    private function isCustomRangeAlgorithmEnabled(Attribute $attribute): bool
    {
        $filterSetting = $this->getFilterSetting($attribute->getAttributeCode());

        return $filterSetting
            && $filterSetting->getDisplayMode() === DisplayMode::MODE_DEFAULT
            && $filterSetting->getRangeAlgorithm() === RangeAlgorithm::CUSTOM;
    }
}
