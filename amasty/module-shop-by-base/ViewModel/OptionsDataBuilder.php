<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\ViewModel;

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBase\Model\OptionSetting;
use Amasty\ShopbyBase\Model\ResourceModel\OptionSetting\Collection;
use Amasty\ShopbyBase\Model\ResourceModel\OptionSetting\CollectionFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\ResourceConnection\SourceProviderInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class OptionsDataBuilder
{
    /**
     * @var CollectionFactory
     */
    private $optionCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Configurable
     */
    private $configurableType;

    public function __construct(
        CollectionFactory $optionCollectionFactory,
        StoreManagerInterface $storeManager,
        Configurable $configurableType
    ) {
        $this->optionCollectionFactory = $optionCollectionFactory;
        $this->storeManager = $storeManager;
        $this->configurableType = $configurableType;
    }

    /**
     * @param Product|ProductInterface $product
     *
     * @return string[]|int[]
     */
    public function getAttributeValues(ProductInterface $product, array $attributeCodes): array
    {
        $values = [];
        if (!count($attributeCodes)) {
            return [];
        }

        foreach ($attributeCodes as $code) {
            $data = $product->getData($code);
            if ($data) {
                $values[] = $this->prepareAttributeValue($data);
            } elseif ($product->getTypeId() === Configurable::TYPE_CODE) {
                /** @var Product[] $childProducts */
                $childProducts = $this->configurableType->getUsedProducts($product);
                foreach ($childProducts as $childProduct) {
                    $childAttrValue = $childProduct->getData($code);
                    if ($childAttrValue) {
                        $values[] = $this->prepareAttributeValue($childAttrValue);
                    }
                }
            }
        }

        return !empty($values) ? array_merge(...$values) : [];
    }

    /**
     * @param string|array $value
     * @return array
     */
    private function prepareAttributeValue($value)
    {
        if (\is_string($value)) {
            return explode(',', $value);
        }

        return $value;
    }

    /**
     * @param string[]|int[] $attributeValues
     *
     * @return Collection
     */
    public function getOptionSettingByValues(array $attributeValues): SourceProviderInterface
    {
        $optionSettingCollection = $this->optionCollectionFactory->create();
        $optionSettingCollection->addStoreData((int)$this->storeManager->getStore()->getId());
        $optionSettingCollection->addFieldToFilter(OptionSetting::VALUE, ['in' => $attributeValues]);

        //default_store options will be rewritten with current_store ones.
        $optionSettingCollection->getSelect()->order(['filter_code ASC', 'main_table.store_id ASC']);

        return $optionSettingCollection;
    }
}
