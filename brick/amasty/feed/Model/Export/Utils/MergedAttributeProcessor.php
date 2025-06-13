<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Export\Utils;

use Amasty\Feed\Api\Data\FeedInterface;
use Amasty\Feed\Model\Export\ProductFactory as ExportProductFactory;
use Amasty\Feed\Model\Field\CustomFieldsValidator;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

class MergedAttributeProcessor
{
    private const ATTR_MARKER = '{}';
    private const PARENT_MARKER = 'parent';

    /**
     * @var ExportProductFactory
     */
    private $exportProductFactory;

    /**
     * @var CustomFieldsValidator|null
     */
    private $customFieldsValidator;

    /**
     * @var array
     */
    private $uniqueRules = [];

    /**
     * @var array
     */
    private $mergedAttrReplacement = [];

    public function __construct(
        ExportProductFactory $exportProductFactory,
        CustomFieldsValidator $customFieldsValidator
    ) {
        $this->exportProductFactory = $exportProductFactory;
        $this->customFieldsValidator = $customFieldsValidator;
    }

    public function prepareAttrReplacements(Collection $collection, FeedInterface $feed): array
    {
        $validIds = $this->retrieveValidIds($collection);
        $this->mergedAttrReplacement = $this->getMergedAttributesReplacement(
            $validIds,
            (int)$collection->getStoreId(),
            $feed
        );

        return $validIds;
    }

    /**
     * @deprecated since 2.8.0
     * @see self::prepareAttrReplacements()
     */
    public function initialize(array $conditions, array $productIds, int $storeId)
    {
        $this->mergedAttrReplacement = [];
    }

    public function execute(Product $product, string $mergedText): string
    {
        $replace = $this->mergedAttrReplacement[$product->getSku()] ?? null;
        if ($replace) {
            return strtr($mergedText, $replace);
        }

        return $mergedText;
    }

    private function retrieveValidIds(Collection $collection): array
    {
        $validIds = [];
        foreach ($collection->getItems() as $product) {
            foreach ($this->customFieldsValidator->getValidRules($product) as $rule) {
                $mergedText = $rule->getFieldResult()['merged_text'] ?? null;
                if ($mergedText !== null) {
                    $validIds[] = $product->getId();
                    $this->uniqueRules[$rule->getId()] = $rule;
                }
            }
        }

        return array_unique($validIds);
    }

    private function prepareMergedText(): array
    {
        $parsedData = [];
        foreach ($this->uniqueRules as $rule) {
            $mergedText = $rule->getFieldResult()['merged_text'] ?? '';
            preg_match_all('/{(.*?)}/', $mergedText, $matches);
            foreach ($matches[0] as $item) {
                $attribute = trim($item, self::ATTR_MARKER);
                $attributeData = explode('|', $attribute, 3);
                $parsedData[$item] = [
                    'type' => $attributeData[0] ?? '',
                    'code' => $attributeData[1] ?? '',
                    'parent' => ($attributeData[2] ?? '') === self::PARENT_MARKER,
                    'attribute' => $attribute
                ];
            }
        }

        return $parsedData;
    }

    private function getAttributes(array $parsedData, bool $isParent = false): array
    {
        $attributes = [];
        foreach ($parsedData as $attrData) {
            if ($attrData['parent'] === $isParent) {
                $attributes[$attrData['type']][$attrData['code']] = $attrData['code'];
            }
        }

        return $attributes;
    }

    private function getMergedAttributesReplacement(array $productIds, int $storeId, FeedInterface $feed): array
    {
        $replace = [];
        if ($productIds) {
            $export = $this->exportProductFactory->create(
                ['storeId' => $storeId, 'feedProfile' => $feed]
            );
            $parsedData = $this->prepareMergedText();
            $exportData = $export->setAttributes($this->getAttributes($parsedData))
                ->setParentAttributes($this->getAttributes($parsedData, true))
                ->setMatchingProductIds($productIds)
                ->getRawExport();

            foreach ($exportData as $sku => $item) {
                foreach ($parsedData as $substr => $attrData) {
                    $replace[$sku][$substr] = $item[$attrData['attribute']] ?? '';
                }
            }

            return $replace;
        }

        return $replace;
    }
}
