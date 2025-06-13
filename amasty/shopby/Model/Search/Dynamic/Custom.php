<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Search\Dynamic;

use Amasty\Base\Model\MagentoVersion;
use Amasty\ShopbyBase\Api\Data\FilterSettingRepositoryInterface;
use Magento\Framework\Search\Adapter\OptionsInterface;
use Magento\Framework\Search\Dynamic\Algorithm\Auto;
use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\Dynamic\EntityStorage;
use Magento\Framework\Search\Request\BucketInterface;

class Custom extends Auto
{
    public const ALGORITHM_CODE = 'shopby_custom';

    /**
     * @var FilterSettingRepositoryInterface
     */
    private $filterSettingRepository;

    /**
     * @var MagentoVersion
     */
    private $magentoVersion;

    /**
     * @var DataProviderInterface
     */
    private $dataProvider;

    public function __construct(
        FilterSettingRepositoryInterface $filterSettingRepository,
        MagentoVersion $magentoVersion,
        DataProviderInterface $dataProvider,
        OptionsInterface $options
    ) {
        $this->filterSettingRepository = $filterSettingRepository;
        $this->magentoVersion = $magentoVersion;
        $this->dataProvider = $dataProvider;
        parent::__construct($dataProvider, $options);
    }

    /**
     * @return array
     */
    public function getItems(BucketInterface $bucket, array $dimensions, EntityStorage $entityStorage)
    {
        $filterSetting = $this->filterSettingRepository->getByAttributeCode($bucket->getField());
        if ($range = $filterSetting->getRangeStep()) {
            $data = [];
            if ($entityStorage->getSource()) {
                $dbRanges = $this->dataProvider->getAggregation($bucket, $dimensions, $range, $entityStorage);
                $data = $this->dataProvider->prepareData($range, $dbRanges);
                if ($this->isCanCalculateRangeBounds()) {
                    $data = $this->updateRangesBound($data, $this->dataProvider->getAggregations($entityStorage));
                }
            }

            return $data;
        }

        return parent::getItems($bucket, $dimensions, $entityStorage);
    }

    /**
     * Update min bound in first range.
     */
    private function updateRangesBound(array $data, array $aggregations): array
    {
        if (reset($data)) {
            $data[0]['from'] = $aggregations['min'];
        }

        return $data;
    }

    /**
     * In oldest magento versions can't get correct min value for custom attribute from data provider.
     * @see \Magento\Elasticsearch\SearchAdapter\Dynamic\DataProvider::getAggregations
     */
    private function isCanCalculateRangeBounds(): bool
    {
        return version_compare($this->magentoVersion->get(), '2.4.4', '>=');
    }
}
