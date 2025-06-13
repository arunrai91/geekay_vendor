<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Block\Product;

use Amasty\ShopbyBase\Model\OptionSetting;
use Amasty\ShopbyBase\ViewModel\OptionProcessorInterface;
use Amasty\ShopbyBase\ViewModel\OptionsDataBuilder;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class AttributeIcon extends Template
{
    public const KEY_ATTRIBUTE_CODES = 'attribute_codes';

    public const PAGE_TYPE = 'page_type';

    public const KEY_PRODUCT = 'product';

    public const KEY_ATTRIBUTE_VALUES = 'attribute_values';

    public const KEY_OPTION_PROCESSOR = 'option_processor';

    /**
     * @var string
     */
    protected $_template = 'Amasty_ShopbyBase::attribute/icon.phtml';

    /**
     * @var OptionsDataBuilder
     */
    private $optionsDataBuilder;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        OptionsDataBuilder $optionsDataBuilder,
        Registry $registry,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->optionsDataBuilder = $optionsDataBuilder;
        $this->registry = $registry;
    }

    /**
     * Initialize block's cache
     *
     * @return void
     */
    protected function _construct(): void
    {
        parent::_construct();

        if (!$this->hasData('cache_lifetime')) {
            $this->setData('cache_lifetime', 86400);
        }
    }

    protected function getCacheTags(): array
    {
        $tags = parent::getCacheTags();
        foreach ($this->getCurrentAttributeValues() as $attributeValue) {
            $tags[] = OptionSetting::CACHE_TAG . '_' . $attributeValue;
        }

        return $tags;
    }

    /**
     * @return array
     */
    public function getCacheKeyInfo(): array
    {
        $parts = parent::getCacheKeyInfo();
        $parts[self::PAGE_TYPE] = $this->getDataByKey(self::PAGE_TYPE);
        foreach ($this->getCurrentAttributeValues() as $attributeValue) {
            $parts[] = 'atrv' . $attributeValue;
        }

        return  $parts;
    }

    /**
     * @return OptionProcessorInterface
     */
    public function getOptionProcessor(): OptionProcessorInterface
    {
        return $this->getDataByKey(self::KEY_OPTION_PROCESSOR);
    }

    /**
     * @param OptionProcessorInterface $optionProcessor
     */
    public function setOptionProcessor(OptionProcessorInterface $optionProcessor): void
    {
        $this->setData(self::KEY_OPTION_PROCESSOR, $optionProcessor);
    }

    /**
     * @return array
     */
    public function getOptionsData(): array
    {
        $data = [];

        $attributeValues = $this->getCurrentAttributeValues();
        if (empty($attributeValues)) {
            return $data;
        }

        $optionSettingCollection = $this->optionsDataBuilder->getOptionSettingByValues($attributeValues);
        foreach ($optionSettingCollection as $optionSetting) {
            try {
                /** @var OptionSetting $optionSetting */
                $data[$optionSetting->getValue()] = $this->getOptionProcessor()->process($optionSetting);
            } catch (NoSuchEntityException $e) {
                continue;
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getCurrentAttributeValues(): array
    {
        if ($this->hasData(self::KEY_ATTRIBUTE_VALUES)) {
            return $this->getData(self::KEY_ATTRIBUTE_VALUES);
        }

        $attributeValues = [];
        $product = $this->getProduct();
        if ($product) {
            $attributeValues = $this->optionsDataBuilder->getAttributeValues($product, $this->getAttributeCodes());
        }

        $this->setData(self::KEY_ATTRIBUTE_VALUES, $attributeValues);

        return $attributeValues;
    }

    /**
     * @return Product|ProductInterface|null
     */
    public function getProduct(): ?ProductInterface
    {
        if ($this->hasData(self::KEY_PRODUCT)) {
            return $this->getData(self::KEY_PRODUCT);
        }

        return $this->registry->registry('current_product');
    }

    /**
     * @return array
     */
    public function getAttributeCodes(): array
    {
        if ($this->hasData(self::KEY_ATTRIBUTE_CODES)) {
            return (array) $this->getData(self::KEY_ATTRIBUTE_CODES);
        }

        return [];
    }

    public function setAttributeCodes(array $attributeCodes): void
    {
        $this->setData(self::KEY_ATTRIBUTE_CODES, $attributeCodes);
    }

    /**
     * @param array $setting
     * @return bool
     */
    public function isShowBrandLink(array $setting): bool
    {
        return ($setting[OptionProcessorInterface::DISPLAY_TITLE] ?? false)
            || ($setting[OptionProcessorInterface::LINK_URL] && !empty($setting[OptionProcessorInterface::IMAGE_URL]))
            || !empty($setting[OptionProcessorInterface::SHORT_DESCRIPTION]);
    }
}
