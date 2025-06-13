<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Ui\Component\Listing\Columns;

use Amasty\ShopbyBase\Api\Data\OptionSettingRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Image extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var OptionSettingRepositoryInterface
     */
    protected $brandRepository;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OptionSettingRepositoryInterface $brandRepository,
        UrlInterface $urlBuilder,
        ImageHelper $imageHelper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->brandRepository = $brandRepository;
        $this->urlBuilder = $urlBuilder;
        $this->imageHelper = $imageHelper->init(null, 'product_listing_thumbnail');
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $storeId = $this->context->getFilterParam('store_id');
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                try {
                    $brand = $this->brandRepository->get($item['option_setting_id']);
                } catch (NoSuchEntityException $e) {
                    continue;
                }

                if ($brand->getId()) {
                    $img = $this->getImage($brand);
                    $item[$fieldName . '_src'] = $img;
                    $item[$fieldName . '_alt'] = $this->getAlt($item);
                    $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                        'amasty_shopbybrand/slider/edit',
                        ['filter_code' => $item['filter_code'], 'option_id' => $item['option_id'], 'store' => $storeId]
                    );
                    $item[$fieldName . '_orig_src'] = $img;
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param array $row
     *
     * @return null|string
     */
    protected function getAlt($row)
    {
        return $row['title'];
    }

    /**
     * @param \Amasty\ShopbyBase\Api\Data\OptionSettingInterface $brand
     * @return null|string
     */
    protected function getImage(\Amasty\ShopbyBase\Api\Data\OptionSettingInterface $brand)
    {
        return $brand->getImageUrl()
            ? $brand->getImageUrl()
            : $this->imageHelper->getDefaultPlaceholderUrl();
    }
}
