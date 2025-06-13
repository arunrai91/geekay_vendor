<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\OptionSource\Feed\CustomOptionSource;

use Amasty\Feed\Model\Category\ResourceModel\Collection as CategoryCollection;
use Amasty\Feed\Model\Category\ResourceModel\CollectionFactory as CategoryCollectionFactory;
use Amasty\Feed\Model\Export\Product\Attributes\FeedAttributesStorage;

class CategoryAttribute implements CustomOptionSourceInterface
{
    /**
     * @var Utils\ArrayCustomizer
     */
    private $arrayCustomizer;

    /**
     * @var Utils\OptionFormatter
     */
    private $optionFormatter;

    /**
     * @var CategoryCollection
     */
    private $categoryCollection;

    /**
     * @var array
     */
    private $options;

    public function __construct(
        Utils\ArrayCustomizer $arrayCustomizer,
        Utils\OptionFormatter $optionFormatter,
        CategoryCollectionFactory $categoryCollectionFactory,
        array $options
    ) {
        $this->arrayCustomizer = $arrayCustomizer;
        $this->optionFormatter = $optionFormatter;
        $this->categoryCollection = $categoryCollectionFactory->create()->addOrder('name');
        $this->options = $options;
    }

    public function getOptions(): array
    {
        $attributes = [];
        foreach ($this->options as $option) {
            $attributes[$this->optionFormatter->getCode($option['code'], $option['type'])] =
                $this->optionFormatter->getTitle($option['option']['title'], $option['option']['code']);
        }
        $attributes += $this->arrayCustomizer->customizeArray(
            $this->categoryCollection->getData(),
            FeedAttributesStorage::PREFIX_MAPPED_CATEGORY_ATTRIBUTE,
            false
        );

        return $attributes;
    }
}
