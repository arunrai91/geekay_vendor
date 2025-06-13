<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Export\RowCustomizer;

use Amasty\Feed\Model\Export\Product\Attributes\FeedAttributesStorage;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magento\Framework\UrlInterface;
use Magento\Sitemap\Model\ResourceModel\Catalog\Product as ProductSitemap;
use Magento\Store\Model\StoreManagerInterface;

class Image implements RowCustomizerInterface
{
    public const THUMBNAIL_TYPE = 'thumbnail';
    public const IMAGE_TYPE = 'image';
    public const SMALL_IMAGE_TYPE = 'small_image';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string
     */
    private $urlPrefix;

    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    public function prepareData($collection, $productIds)
    {
        $this->urlPrefix = $this->storeManager->getStore($collection->getStoreId())
                ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
            . 'catalog/product';

        return $this;
    }

    public function addData($dataRow, $productId)
    {
        $customData = &$dataRow[Composite::CUSTOM_DATA_KEY];

        $customData[FeedAttributesStorage::PREFIX_IMAGE_ATTRIBUTE] = [
            self::THUMBNAIL_TYPE => $this->checkImage($dataRow, self::THUMBNAIL_TYPE),
            self::IMAGE_TYPE => $this->checkImage($dataRow, self::IMAGE_TYPE),
            self::SMALL_IMAGE_TYPE => $this->checkImage($dataRow, self::SMALL_IMAGE_TYPE),
        ];

        return $dataRow;
    }

    public function addHeaderColumns($columns)
    {
        return $columns;
    }

    public function getAdditionalRowsCount($additionalRowsCount, $productId)
    {
        return $additionalRowsCount;
    }

    private function checkImage(array $dataRow, string $imgType): ?string
    {
        if (isset($dataRow[$imgType])
            && $dataRow[$imgType] !== ProductSitemap::NOT_SELECTED_IMAGE
        ) {
            $dataRow[$imgType] = '/' . ltrim($dataRow[$imgType], '/');

            return $this->urlPrefix . $dataRow[$imgType];
        }

        return null;
    }
}
