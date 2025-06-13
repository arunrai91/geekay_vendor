<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Layer\Filter;

use Amasty\Shopby\Model\ConfigProvider;
use Amasty\Shopby\Model\Layer\Filter\Resolver\FilterRequestDataResolver;
use Amasty\Shopby\Model\Layer\Filter\Resolver\FilterSettingResolver;
use Amasty\ShopbyBase\Model\FilterSetting\IsMultiselect;
use Amasty\ShopbyBase\Model\OptionSettingRepository;
use Magento\Catalog\Model\Layer;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Search\Api\SearchInterface;
use Magento\Store\Model\StoreManagerInterface;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filter\StripTags as TagFilter;
use Magento\Catalog\Model\Layer\Filter\ItemFactory as FilterItemFactory;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder as ItemDataBuilder;
use Amasty\Shopby\Model\Search\RequestGenerator as ShopbyRequestGenerator;
use Magento\Store\Model\Store;
use Magento\Framework\App\RequestInterface;
use Amasty\Shopby\Model\Source\SortOptionsBy;
use Magento\Framework\Message\ManagerInterface as MessageManager;

class Attribute extends AbstractFilter
{
    /**
     * @var TagFilter
     */
    private $tagFilter;

    /**
     * @var SearchInterface
     */
    private $search;

    /**
     * @var OptionSettingRepository
     */
    private $optionSettingRepository;

    /**
     * @var MessageManager
     */
    private $messageManager;

    /**
     * @var FilterRequestDataResolver
     */
    private $filterRequestDataResolver;

    /**
     * @var FilterSettingResolver
     */
    private $filterSettingResolver;

    /**
     * @var IsMultiselect
     */
    private $isMultiselect;

    /**
     * @var bool
     */
    private $isVisible;

    /**
     * @var array
     */
    private $facets;

    /**
     * @var ConfigProvider|null
     */
    private $configProvider;

    public function __construct(
        FilterItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        ItemDataBuilder $itemDataBuilder,
        TagFilter $tagFilter,
        SearchInterface $search,
        OptionSettingRepository $optionSettingRepository,
        MessageManager $messageManager,
        FilterRequestDataResolver $filterRequestDataResolver,
        FilterSettingResolver $filterSettingResolver,
        IsMultiselect $isMultiselect,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $data
        );

        $this->tagFilter = $tagFilter;
        $this->search = $search;
        $this->optionSettingRepository = $optionSettingRepository;
        $this->messageManager = $messageManager;
        $this->filterRequestDataResolver = $filterRequestDataResolver;
        $this->filterSettingResolver = $filterSettingResolver;
        $this->isMultiselect = $isMultiselect;
        $this->configProvider = $configProvider;
    }

    /**
     * Apply attribute option filter to product collection.
     *
     * @param RequestInterface $request
     * @return $this
     * @throws LocalizedException
     */

    public function apply(RequestInterface $request)
    {
        if ($this->filterRequestDataResolver->isApplied($this)) {
            return $this;
        }

        $requestedOptions = $this->getValidRequestedOptions();

        if (empty($requestedOptions)) {
            return $this;
        }

        $this->filterRequestDataResolver->setCurrentValue($this, $requestedOptions);
        $this->addState($requestedOptions);

        if (!$this->filterSettingResolver->isMultiSelectAllowed($this) && count($requestedOptions) > 1) {
            $requestedOptions = array_slice($requestedOptions, 0, 1);
        }

        $attribute = $this->getAttributeModel();

        /** @var \Amasty\Shopby\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();
        if ($this->isUseAndLogic()) {
            foreach ($requestedOptions as $key => $value) {
                $fakeAttributeCode = $this->getFakeAttributeCodeForApply($attribute->getAttributeCode(), $key);
                $productCollection->addFieldToFilter($fakeAttributeCode, $value);
            }
        } else {
            $productCollection->addFieldToFilter($attribute->getAttributeCode(), $requestedOptions);
        }

        return $this;
    }

    private function getValidRequestedOptions(): array
    {
        $requestedOptionsString = $this->filterRequestDataResolver->getFilterParam($this);
        if (empty($requestedOptionsString) && !is_numeric($requestedOptionsString)) {
            return [];
        }

        $requestedOptions = explode(',', $requestedOptionsString);
        $availableOptions = $this->getOptionIds();
        foreach ($requestedOptions as $key => $option) {
            if (!in_array($option, $availableOptions)) {
                // clear wrong value. can't parse to int to make it work with Grouped options
                $requestedOptions[$key] = '0';
            }
        }

        return $requestedOptions;
    }

    private function isUseAndLogic(): bool
    {
        $filterSetting = $this->filterSettingResolver->getFilterSetting($this);
        $isMultiselect = $this->isMultiselect->execute(
            $filterSetting->getAttributeCode(),
            $filterSetting->isMultiselect(),
            $filterSetting->getDisplayMode()
        );

        return $filterSetting->isUseAndLogic() && $isMultiselect;
    }

    /**
     * @param array $values
     * @throws LocalizedException
     */
    private function addState(array $values)
    {
        if (!$this->shouldAddState()) {
            return;
        }

        foreach ($values as $value) {
            $item = $this->_createItem($this->getOptionText($value), $value);
            $this->getLayer()->getState()
                ->addFilter(
                    $item
                );
        }
    }

    /**
     * @return bool
     */
    public function shouldAddState()
    {
        // Could be overwritten in plugins.
        return true;
    }

    /**
     * @param string $attributeCode
     * @param $key
     * @return string
     */
    private function getFakeAttributeCodeForApply($attributeCode, $key)
    {
        if ($key > 0) {
            $attributeCode .= ShopbyRequestGenerator::FAKE_SUFFIX . $key;
        }

        return $attributeCode;
    }

    /**
     * @return int
     */
    public function getItemsCount()
    {
        if (!$this->isFilterVisible()) {
            return 0;
        }

        return count($this->getFacets());
    }

    /**
     * @param array $a
     * @param array $b
     * @return int
     */
    public function sortOption($a, $b)
    {
        $a['label'] = (string)$a['label'];
        $b['label'] = (string)$b['label'];
        $pattern = '@^(\d+)@';
        if (preg_match($pattern, $a['label'], $ma) && preg_match($pattern, $b['label'], $mb)) {
            $r = (int)$ma[1] - (int)$mb[1];
            if ($r != 0) {
                return $r;
            }
        }

        return strcasecmp($a['label'], $b['label']);
    }

    private function isFilterVisible(): bool
    {
        if ($this->isVisible === null) {
            $this->isVisible = true;
            $value = $this->filterRequestDataResolver->getFilterParam($this);
            if ((!empty($value) || is_numeric($value))
                && !$this->filterRequestDataResolver->isVisibleWhenSelected($this)
            ) {
                return $this->isVisible = false;
            }

            $optionsFacetedData = $this->getFacets();
            if (empty($optionsFacetedData)) {
                return $this->isVisible = false;
            }
        }

        return $this->isVisible;
    }

    /**
     * Get data array for building attribute filter items.
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getItemsData()
    {
        if (!$this->isFilterVisible()) {
            return [];
        }

        $itemsData = $this->getFacets();
        $this->sortItemsByFeatured($itemsData);

        return $itemsData;
    }

    private function getFacets()
    {
        if ($this->facets === null) {
            $options = $this->getOptions();
            $optionsFacetedData = $this->getOptionsFacetedData();

            $this->addItemsToDataBuilder($options, $optionsFacetedData);

            $this->facets = $this->getItemsFromDataBuilder();
        }

        return $this->facets;
    }

    /**
     * @return array
     */
    private function getOptions()
    {
        $attribute = $this->getAttributeModel();
        $options = $attribute->getFrontend()->getSelectOptions();

        if ($this->filterSettingResolver->getFilterSetting($this)->getSortOptionsBy() == SortOptionsBy::NAME) {
            usort($options, [$this, 'sortOption']);
        }

        return $options;
    }

    private function getOptionIds(): array
    {
        return array_column($this->getOptions() ?? [], 'value');
    }

    /**
     * Additional Sort options by is_featured setting
     */
    private function sortItemsByFeatured(array &$options): array
    {
        $attribute = $this->getAttributeModel();
        $featuredOptionArray = [];
        $nonFeaturedOptionArray = [];
        $featuredOptions = $this->optionSettingRepository->getAllFeaturedOptionsArray($this->getStoreId());
        foreach ($options as $option) {
            if ($this->isOptionFeatured($featuredOptions, $attribute->getAttributeCode(), $option)) {
                $featuredOptionArray[] = $option;
            } else {
                $nonFeaturedOptionArray[] = $option;
            }
        }
        $options = array_merge($featuredOptionArray, $nonFeaturedOptionArray);
        $filterSetting = $this->filterSettingResolver->getFilterSetting($this);
        if (count($featuredOptionArray)
            && count($nonFeaturedOptionArray)
            && !$filterSetting->getNumberUnfoldedOptions()
        ) {
            $filterSetting->setNumberUnfoldedOptions(count($featuredOptionArray));
        }

        return $options;
    }

    /**
     * @param array $options
     * @param string $attributeCode
     * @param array $option
     * @return bool
     */
    private function isOptionFeatured($options, $attributeCode, $option)
    {
        return isset($options[$attributeCode][$option['value']][$this->getStoreId()])
            || isset($options[$attributeCode][$option['value']][Store::DEFAULT_STORE_ID]);
    }

    /**
     * @return array
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getOptionsFacetedData()
    {
        $optionsFacetedData = $this->generateOptionsFacetedData();

        if (count($optionsFacetedData)) {
            $optionsFacetedData = $this->convertOptionsFacetedData($optionsFacetedData);
        }

        return $optionsFacetedData;
    }

    /**
     * @param array $optionsFacetedData
     *
     * @return array
     */
    private function convertOptionsFacetedData($optionsFacetedData)
    {
        $attributeValue = $this->filterRequestDataResolver->getFilterParam($this);
        if ($attributeValue) {
            $values = explode(',', $attributeValue);
            foreach ($values as $value) {
                if (!empty($value) && !array_key_exists($value, $optionsFacetedData)) {
                    $optionsFacetedData[$value] = ['value' => $value, 'count' => 0];
                }
            }
        }

        return $optionsFacetedData;
    }

    /**
     * @return array
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function generateOptionsFacetedData()
    {
        /** @var \Amasty\Shopby\Model\ResourceModel\Fulltext\Collection $productCollectionOrigin */
        $productCollectionOrigin = $this->getLayer()->getProductCollection();
        $attribute = $this->getAttributeModel();

        try {
            $optionsFacetedData = $productCollectionOrigin->getFacetedData(
                $attribute->getAttributeCode(),
                $this->getSearchResult()
            );
        } catch (StateException $e) {
            if (!$this->messageManager->hasMessages()) {
                $this->messageManager->addErrorMessage(
                    __(
                        'Make sure that "%1" attribute can be used in layered navigation',
                        $attribute->getAttributeCode()
                    )
                );
            }
            $optionsFacetedData = [];
        }

        return $optionsFacetedData;
    }

    private function getSearchResult(): ?SearchResultInterface
    {
        $searchResult = null;

        if ($this->filterRequestDataResolver->hasCurrentValue($this)
            && !$this->filterSettingResolver->getFilterSetting($this)->isUseAndLogic()
        ) {
            $searchCriteria = $this->getLayer()->getProductCollection()
                ->getSearchCriteria([$this->getAttributeModel()->getAttributeCode()]);
            $searchResult = $this->search->search($searchCriteria);
        }

        return $searchResult;
    }

    /**
     * @param array $options
     * @param array $optionsFacetedData
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    private function addItemsToDataBuilder($options, $optionsFacetedData)
    {
        if (!$options) {
            return;
        }

        if ($this->filterSettingResolver->getFilterSetting($this)->getSortOptionsBy() == SortOptionsBy::PRODUCT_COUNT) {
            usort($options, static function ($a, $b) use ($optionsFacetedData) {
                $aCount = $optionsFacetedData[$a['value']]['count'] ?? 0;
                $bCount = $optionsFacetedData[$b['value']]['count'] ?? 0;

                return $bCount <=> $aCount;
            });
        }

        foreach ($options as $option) {
            if (empty($option['value']) && !is_numeric($option['value'])) {
                continue;
            }

            $isFilterableAttribute = $this->getAttributeIsFilterable($this->getAttributeModel());
            if (isset($optionsFacetedData[$option['value']])
                || $isFilterableAttribute != static::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS
            ) {
                $count = 0;
                if (isset($optionsFacetedData[$option['value']]['count'])) {
                    $count = $optionsFacetedData[$option['value']]['count'];
                }
                $this->itemDataBuilder->addItemData(
                    $this->tagFilter->filter($option['label']),
                    $option['value'],
                    $count
                );
            }
        }
    }

    /**
     * Get items data according to attribute settings.
     * @return array
     */
    private function getItemsFromDataBuilder()
    {
        $itemsData = $this->itemDataBuilder->build();

        if ($this->configProvider->isHideFilterWithOneOption()
            && count($itemsData) == 1
            && !$this->isOptionReducesResults(
                $itemsData[0]['count'],
                $this->getLayer()->getProductCollection()->getSize()
            )
        ) {
            return $this->filterRequestDataResolver->getReducedItemsData($this, $itemsData);
        }

        return $itemsData;
    }
}
