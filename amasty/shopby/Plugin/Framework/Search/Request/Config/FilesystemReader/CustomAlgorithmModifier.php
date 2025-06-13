<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Framework\Search\Request\Config\FilesystemReader;

use Amasty\Shopby\Model\Search\Dynamic\Custom;
use Amasty\Shopby\Model\Source\DisplayMode;
use Amasty\Shopby\Model\Source\RangeAlgorithm;
use Amasty\ShopbyBase\Api\Data\FilterSettingRepositoryInterface;
use Magento\Framework\Config\ReaderInterface;

class CustomAlgorithmModifier
{
    private const PRICE_ATTRIBUTE_CODE = 'price';

    /**
     * @var FilterSettingRepositoryInterface
     */
    private $filterSettingRepository;

    public function __construct(FilterSettingRepositoryInterface $filterSettingRepository)
    {
        $this->filterSettingRepository = $filterSettingRepository;
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRead(
        ReaderInterface $subject,
        array $result
    ) {
        if (!$this->isCustomRangeAlgorithmEnabled()) {
            return $result;
        }

        $bucketName = self::PRICE_ATTRIBUTE_CODE . '_bucket';
        foreach ($result as &$request) {
            if (isset($request['aggregations'][$bucketName])) {
                $request['aggregations'][$bucketName]['method'] = Custom::ALGORITHM_CODE;
            }
        }

        return $result;
    }

    private function isCustomRangeAlgorithmEnabled(): bool
    {
        $filterSetting = $this->filterSettingRepository->getByAttributeCode(self::PRICE_ATTRIBUTE_CODE);

        return $filterSetting
            && $filterSetting->getDisplayMode() === DisplayMode::MODE_DEFAULT
            && $filterSetting->getRangeAlgorithm() === RangeAlgorithm::CUSTOM;
    }
}
