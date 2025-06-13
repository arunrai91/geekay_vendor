<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Plugin\Framework\Search\Request\Config\FilesystemReader;

use Amasty\ShopbyBrand\Model\ConfigProvider;
use Magento\Framework\Search\Request\Config\FilesystemReader;

class AddBrandSearchRequest
{
    public const BRAND_SEARCH_REQUEST_NAME = 'amasty_brand_counter';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param FilesystemReader $subject
     * @param array $result
     * @return array
     */
    public function afterRead(FilesystemReader $subject, $result)
    {
        if (!is_array($result)) {
            return $result;
        }

        $brandBucketNames = array_map(function (string $brandAttributesCode) {
            return $brandAttributesCode . '_bucket';
        }, $this->configProvider->getAllBrandAttributeCodes());

        $brandSearchRequest = $result['catalog_view_container'];
        $brandAggregations = array_filter(
            $brandSearchRequest['aggregations'],
            static function (string $bucketName) use ($brandBucketNames) {
                return in_array($bucketName, $brandBucketNames, true);
            },
            ARRAY_FILTER_USE_KEY
        );
        $brandSearchRequest['aggregations'] = $brandAggregations;
        $result[self::BRAND_SEARCH_REQUEST_NAME] = $brandSearchRequest;

        return $result;
    }
}
