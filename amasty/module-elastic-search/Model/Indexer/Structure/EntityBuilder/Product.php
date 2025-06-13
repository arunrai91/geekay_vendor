<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Indexer\Structure\EntityBuilder;

use Amasty\ElasticSearch\Api\Data\Indexer\Structure\EntityBuilderInterface;
use Amasty\ElasticSearch\Model\Config as ConfigProvider;
use Amasty\ElasticSearch\Model\Indexer\Structure\AnalyzerBuilder\DefaultBuilder;
use Amasty\ElasticSearch\Utils\AttributeUtil;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ObjectManager;

class Product implements EntityBuilderInterface
{
    public const ATTRIBUTE_TYPE_TEXT       = 'text';
    public const ATTRIBUTE_TYPE_KEYWORD    = 'keyword';
    public const ATTRIBUTE_TYPE_FLOAT      = 'float';
    public const ATTRIBUTE_TYPE_INT        = 'integer';
    public const ATTRIBUTE_TYPE_DATE       = 'date';
    public const DEFAULT_ATTRIBUTE_CODE_ARRAY = ['category_ids', 'visibility'];

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var array
     */
    private $customerGroupIds;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var AttributeUtil
     */
    private $attributeUtil;

    public function __construct(
        Config $eavConfig,
        ConfigProvider $configProvider,
        CollectionFactory $customerGroupCollectionFactory,
        ?AttributeUtil $attributeUtil = null // TODO: move to not optional argument and remove OM
    ) {
        $this->eavConfig = $eavConfig;
        $this->configProvider = $configProvider;
        $customerGroupCollection = $customerGroupCollectionFactory->create();
        $this->customerGroupIds = $customerGroupCollection->getAllIds();
        $this->attributeUtil = $attributeUtil ?? ObjectManager::getInstance()->get(AttributeUtil::class);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function buildEntityFields()
    {
        $attributeCodes = $this->eavConfig->getEntityAttributeCodes(ProductAttributeInterface::ENTITY_TYPE_CODE);
        $allAttributes = [];
        $useCustomAnalyzer = $this->configProvider->useCustomAnalyzer();

        foreach ($attributeCodes as $attributeCode) {
            $attribute = $this->eavConfig->getAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode);
            $attributeType = $this->getAttributeType($attribute);
            $allAttributes[$attributeCode] = ['type' => $attributeType];

            if ($attributeCode == "category_ids") {
                $allAttributes[$attributeCode] = [
                    'type' => self::ATTRIBUTE_TYPE_INT
                ];
            } elseif ($attributeCode == "sku") {
                $allAttributes[$attributeCode]['fielddata'] = true;
            }

            if ($allAttributes[$attributeCode]['type'] == self::ATTRIBUTE_TYPE_DATE) {
                $allAttributes[$attributeCode]['format'] = 'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis';
            } elseif ($attributeCode == "price") {
                foreach ($this->customerGroupIds as $groupId) {
                    $allAttributes[$attributeCode . '_' . $groupId] = ['type' => self::ATTRIBUTE_TYPE_FLOAT];
                }
            }

            if (!$this->attributeUtil->isComplexAttribute($attribute)
                && $attribute->getUsedForSortBy()
                && $attributeType == self::ATTRIBUTE_TYPE_TEXT
            ) {
                $sortFieldType = $this->getSortFieldType($attribute);

                $textAttributeConfig = [
                    'type' => $sortFieldType,
                    'index' => false,
                ];

                if (!$useCustomAnalyzer && $sortFieldType === self::ATTRIBUTE_TYPE_KEYWORD) {
                    $textAttributeConfig['normalizer'] = DefaultBuilder::LOWERCASE_NORMALIZER;
                }

                $allAttributes[$attributeCode]['fields']['sort_' . $attributeCode] = $textAttributeConfig;
            }

            if ($this->attributeUtil->isComplexAttribute($attribute)) {
                $allAttributes[$attributeCode]['type'] = self::ATTRIBUTE_TYPE_KEYWORD;
                $allAttributes[$attributeCode . '_value']['type'] = self::ATTRIBUTE_TYPE_TEXT;

                if ($attribute->getUsedForSortBy()) {
                    $sortFieldType = $this->getSortFieldType($attribute);

                    $allAttributes[$attributeCode . '_value']['fields']['sort_' . $attributeCode] = [
                        'type' => $sortFieldType,
                        'index' => false,
                    ];
                }
            }
        }

        return $allAttributes;
    }

    /**
     * @param AbstractAttribute $attribute
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isSearchable(AbstractAttribute $attribute)
    {
        return $attribute->getIsSearchable()
            && ($attribute->getIsVisibleInAdvancedSearch()
                || $attribute->getIsFilterable()
                || $attribute->getIsFilterableInSearch()
            )
            || (in_array($attribute->getAttributeCode(), self::DEFAULT_ATTRIBUTE_CODE_ARRAY, true));
    }

    /**
     * @param AbstractAttribute $attribute
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttributeType(AbstractAttribute $attribute)
    {
        $backendType = $attribute->getBackendType();
        $frontendInput = $attribute->getFrontendInput();

        if ((in_array($backendType, ['int', 'smallint'], true)
                || (in_array($frontendInput, ['select', 'boolean'], true) && $backendType !== 'varchar'))
            && !$attribute->getIsUserDefined()
        ) {
            $fieldType = self::ATTRIBUTE_TYPE_INT;
        } elseif ($backendType === 'decimal') {
            $fieldType = self::ATTRIBUTE_TYPE_FLOAT;
        } else {
            if (!$this->isSearchable($attribute)) {
                if ($attribute->getIsFilterable() || $attribute->getIsFilterableInSearch()) {
                    $fieldType = self::ATTRIBUTE_TYPE_KEYWORD;
                } else {
                    $fieldType = self::ATTRIBUTE_TYPE_TEXT;
                }
            } else {
                $fieldType = self::ATTRIBUTE_TYPE_TEXT;
            }
        }

        return $fieldType;
    }

    public function getSortFieldType(AbstractAttribute $attribute): string
    {
        $frontendInput = $attribute->getFrontendClass();

        switch ($frontendInput) {
            case 'validate-number':
                return self::ATTRIBUTE_TYPE_FLOAT;
            case 'validate-digits':
                return self::ATTRIBUTE_TYPE_INT;
            default:
                return self::ATTRIBUTE_TYPE_KEYWORD;
        }
    }
}
