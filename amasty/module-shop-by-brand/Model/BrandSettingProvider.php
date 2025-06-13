<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Model;

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBase\Model\OptionSetting;
use Amasty\ShopbyBase\Model\ResourceModel\OptionSetting\CollectionFactory;
use Amasty\ShopbyBrand\Helper\Data;

class BrandSettingProvider
{
    /**
     * @var array
     */
    private $brandSettingsByStore = [];

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Data
     */
    private $brandHelper;

    public function __construct(
        CollectionFactory $collectionFactory,
        Data $brandHelper
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->brandHelper = $brandHelper;
    }

    /**
     * @param int $storeId
     *
     * @return array
     */
    public function getItemsByStoreId(int $storeId): array
    {
        if (!isset($this->brandSettingsByStore[$storeId])) {
            $this->brandSettingsByStore[$storeId] = [];
            $attributeCode = $this->brandHelper->getBrandAttributeCode();

            if ($attributeCode) {
                $stores = [0,  $storeId];
                $collection = $this->collectionFactory->create()
                    ->addFieldToFilter('store_id', $stores)
                    ->addFieldToFilter(OptionSettingInterface::ATTRIBUTE_CODE, $attributeCode)
                    ->addOrder('store_id', 'DESC');

                /** @var OptionSettingInterface $item **/
                foreach ($collection as $item) {
                    $this->populateBrandSettings($item, $storeId);
                }
            }
        }

        return $this->brandSettingsByStore[$storeId];
    }

    private function populateBrandSettings(OptionSettingInterface $item, int $storeId): void
    {
        if (!isset($this->brandSettingsByStore[$storeId][$item->getValue()])) {
            $this->brandSettingsByStore[$storeId][$item->getValue()] = $item;
        } else {
            $settingByStore = $this->brandSettingsByStore[$storeId][$item->getValue()];
            foreach ($settingByStore->getData() as $key => $value) {
                if ($value === null) {
                    $settingByStore->setData($key, $item->getData($key));
                }
            }
        }
    }

    /**
     * @param int $storeId
     * @param int $value
     *
     * @return OptionSetting|null
     */
    public function getItemByStoreIdAndValue(int $storeId, int $value): ?OptionSetting
    {
        return $this->getItemsByStoreId($storeId)[$value] ?? null;
    }
}
