<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model\SeoOptionsModifier;

use Amasty\ShopbySeo\Helper\Data;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;

class YesNoAliases implements SeoModifierInterface
{
    public const ATTRIBUTE_TYPE_BOOLEAN = 'boolean';

    public const VALUE_YES = 1;
    public const VALUE_NO = 0;
    public const LABEL_YES = 'yes';
    public const LABEL_NO = 'no';

    /**
     * @var Data
     */
    private $seoHelper;

    /**
     * @var CollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @var array
     */
    private $optionValues = [];

    public function __construct(
        Data $seoHelper,
        CollectionFactory $attributeCollectionFactory,
        array $optionValues = []
    ) {
        $this->seoHelper = $seoHelper;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->optionValues = $optionValues;
    }

    /**
     * Method modifies seo attributes options data
     *
     * @param array $optionsSeoData
     * @param int $storeId
     * @param array $attributeIds
     */
    public function modify(array &$optionsSeoData, int $storeId, array &$attributeIds = []): void
    {
        if ($this->isValid()) {
            foreach ($this->getAttributesCollection() as $attribute) {
                $attributeCode = (string)$attribute->getAttributeCode();
                if (!array_key_exists($attribute->getAttributeId(), $attributeIds)) {
                    $attributeIds[$attribute->getAttributeId()] = $attributeCode;
                }

                foreach ($this->getOptionValues() as $optionValue => $optionLabel) {
                    $optionsSeoData[$storeId][$attributeCode][$optionValue] = $optionLabel;
                }
            }
        }
    }

    private function getAttributesCollection(): array
    {
        $seoAttributeCodes = $this->seoHelper->getSeoSignificantAttributeCodes();

        $collection = $this->attributeCollectionFactory->create()
            ->setCodeFilter($seoAttributeCodes)
            ->setFrontendInputTypeFilter(self::ATTRIBUTE_TYPE_BOOLEAN);

        return $collection->getItems();
    }

    private function isValid(): bool
    {
        return $this->seoHelper->isModuleEnabled() && $this->seoHelper->isIncludeAttributeName();
    }

    private function getOptionValues(): array
    {
        if (empty($this->optionValues)) {
            $this->optionValues = [self::VALUE_NO => self::LABEL_NO, self::VALUE_YES => self::LABEL_YES];
        }

        return $this->optionValues;
    }
}
