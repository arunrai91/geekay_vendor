<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Model\Rule\Action\Discount;

use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem as AbstractQuoteItem;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\Data as DiscountData;
use Magento\SalesRule\Model\Rule as RuleModel;

/**
 * Class AbstractSetof
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
abstract class AbstractSetof extends AbstractRule
{
    public const DEFAULT_SORT_ORDER = 'asc';

    /**
     * @var array
     */
    public static $cachedDiscount = [];

    /**
     * @var array|null
     */
    public static $allItems;

    /**
     * @param RuleModel $rule
     * @param AbstractQuoteItem $item
     * @param float $qty
     *
     * @return DiscountData Data
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function calculate($rule, $item, $qty)
    {
        $this->beforeCalculate($rule);

        if (!isset(self::$allItems)) {
            self::$allItems = $this->getSortedItems($item->getAddress(), $rule, self::DEFAULT_SORT_ORDER);
        }

        $discountData = $this->calculateDiscount($rule, $item);
        $this->afterCalculate($discountData, $rule, $item);

        return $discountData;
    }

    /**
     * @param Rule $rule
     * @param AbstractQuoteItem $item
     * @return bool
     */
    public function skip($rule, $item)
    {
        $parent = $item->getParentItem();

        if ($parent && $parent->getProduct()->getTypeId() === Type::TYPE_BUNDLE) {
            return true;
        }

        return parent::skip($rule, $item);
    }

    /**
     * @param AbstractQuoteItem[] $itemsForSet
     * @return AbstractQuoteItem[]
     */
    protected function populateItemsForSet(array $itemsForSet): array
    {
        $childItems = [];

        foreach ($itemsForSet as $key => $item) {
            if ($item->getProduct()->getTypeId() === Type::TYPE_BUNDLE && $item->isChildrenCalculated()) {
                $childItems[] = $item->getChildren();
                unset($itemsForSet[$key]);
            }
        }
        $childItems = array_merge(...$childItems);

        return array_merge($itemsForSet, $childItems);
    }

    /**
     * Calculate discount for rule once. Cached values is returned the next times
     *
     * @param RuleModel $rule
     * @param AbstractQuoteItem $item
     *
     * @return DiscountData
     *
     * @throws \Exception
     */
    protected function calculateDiscount($rule, $item)
    {
        $ruleId = $this->getRuleId($rule);

        if (!array_key_exists($ruleId, self::$cachedDiscount)) {
            $this->calculateDiscountForRule($rule, $item);
        }

        return self::$cachedDiscount[$ruleId][$this->getUniqueProductIdentifier($item)]
            ?? $this->discountFactory->create();
    }

    /**
     * Realize this function to calculate discount.
     *
     * @param RuleModel $rule
     * @param AbstractQuoteItem $item
     *
     * @return mixed
     */
    abstract protected function calculateDiscountForRule($rule, $item);

    /**
     * Determining the elements that will make up the set and the number of sets
     *
     * @param RuleModel $rule
     *
     * @return array|null
     */
    protected function prepareDataForCalculation($rule)
    {
        $promoSkus = $rule->getAmrulesRule()->getPromoSkus();
        $promoCategories = $rule->getAmrulesRule()->getPromoCats();

        if ($promoSkus || $promoCategories) {
            return $this->getItemsForSet($rule);
        }

        return null;
    }

    /**
     * @param Item[] $itemsForSet
     * @param int[] $qtySkus
     * @return Item[]
     */
    private function removeExcessItems(array $itemsForSet, array $qtySkus): array
    {
        $resultItemsForSet = [];

        while (!empty($qtySkus)) {
            foreach ($itemsForSet as $key => $item) {
                $sku = '';
                if (isset($qtySkus[$item->getProduct()->getData('sku')])) {
                    $sku = $item->getProduct()->getData('sku');

                } elseif (isset($qtySkus[$item->getSku()])) {
                    $sku = $item->getSku();
                }
                if ($sku) {
                    $qtySkus[$sku] -= 1;
                    if ($qtySkus[$sku] === 0) {
                        unset($qtySkus[$sku]);
                    }
                    $resultItemsForSet[] = $item;
                    unset($itemsForSet[$key]);
                }
            }
        }

        return $resultItemsForSet;
    }

    /**
     * Determining the elements that will make up the set
     *
     * @param RuleModel $rule
     *
     * @return array
     */
    protected function getItemsForSet($rule)
    {
        $skuSetQty = $setQty = 0;
        $qtySkus = [];
        $itemsForSet = self::$allItems ?: [];
        $skus = $this->rulesDataHelper->getRuleSkus($rule);

        foreach ($skus as $sku) {
            $qtySkus[$sku] = 0;
        }

        foreach ($itemsForSet as $i => $item) {
            //check bundle and configurable parent SKU
            if (in_array($item->getProduct()->getData('sku'), $skus, true)) {
                $qtySkus[$item->getProduct()->getData('sku')]++;
            } elseif (in_array($item->getSku(), $skus, true)) {
                $qtySkus[$item->getSku()]++;
            } else {
                unset($itemsForSet[$i]);
            }
        }

        if ($skus) {
            $skuSetQty = $qtySkus ? min($qtySkus) : 0;
            if ($rule->getDiscountQty() != null) {
                $skuSetQty = min($skuSetQty, (int)$rule->getDiscountQty());
            }

            // There is no items suitable for 'Set Items by SKU' setting
            if ($skuSetQty <= 0) {
                return null;
            }
        }

        $categories = $this->rulesDataHelper->getRuleCats($rule);
        if (!$categories) {
            $qtySkus = array_fill_keys(array_keys($qtySkus), $skuSetQty);
            return [
                $skuSetQty,
                $this->removeExcessItems($itemsForSet, $qtySkus)
            ];
        }

        if ($arrayForCategoriesSet = array_diff_key(self::$allItems ?: [], $itemsForSet)) {
            $categorySets = $this->getCategorySets($this->getCategoriesMatrix($categories, $arrayForCategoriesSet));
            $categorySetQty = count($categorySets);
            $setQty = $skus ? min($skuSetQty, $categorySetQty) : $categorySetQty;
            if ($rule->getDiscountQty() != null) {
                $setQty = min($setQty, (int)$rule->getDiscountQty());
            }
            if ($setQty < $categorySetQty) {
                $categorySets = array_slice($categorySets, 0, $setQty);
            }
            $qtySkus = array_fill_keys(array_keys($qtySkus), $setQty);

            $qtyCategories = $this->reformatResult($categorySets);
            if ($qtyCategories) {
                $qtySkus += $qtyCategories;
                $itemsForSet = self::$allItems;

                foreach ($itemsForSet as $i => $item) {
                    if (!array_key_exists($item->getProduct()->getData('sku'), $qtySkus)) {
                        unset($itemsForSet[$i]);
                    }
                }
            } else {
                $qtySkus = [];
            }
        } else {
            $qtySkus = [];
        }

        return [$setQty, $this->removeExcessItems($itemsForSet, $qtySkus)];
    }

    /**
     * @param array[] $categorySets [['category_id' => 'sku']...]
     * @return int[] ['sku' => 'qty']
     */
    private function reformatResult(array $categorySets): array
    {
        $result = [];

        foreach ($categorySets as $items) {
            foreach ($items as $sku) {
                if (isset($result[$sku])) {
                    $result[$sku] ++;
                } else {
                    $result[$sku] = 1;
                }
            }
        }

        return $result;
    }

    /**
     * @param array[] $categoriesMatrix ['category_id' => ['sku' => 'qty']...]
     * @return array[] [['category_id' => 'sku']...]
     */
    private function getCategorySets(array $categoriesMatrix): array
    {
        $productSets = [];
        $productSetCounter = 1;
        $enableSet = true;
        $qtyItemsInSet = count($categoriesMatrix);
        $categoryIds = array_keys($categoriesMatrix);
        while ($enableSet) {
            foreach ($categoryIds as $categoryId) {
                $items = $categoriesMatrix[$categoryId];
                if (is_array($items) && $items) {
                    $firstItemSku = array_key_first($items);
                    $productSets[$productSetCounter][$categoryId] = $firstItemSku;
                    $categoriesMatrix = $this->unsetItemFromCategories($categoriesMatrix, $firstItemSku);
                }
            }
            if (!isset($productSets[$productSetCounter])
                || (count($productSets[$productSetCounter]) < $qtyItemsInSet)) {
                unset($productSets[$productSetCounter]);
                $enableSet = false;
            }
            $productSetCounter++;
        }

        return $productSets;
    }

    /**
     * @param array $categoriesMatrix ['category_id' => ['sku' => 'qty']...]
     * @param string $sku
     * @return array ['category_id' => ['sku' => 'qty']...]
     */
    private function unsetItemFromCategories(array $categoriesMatrix, string $sku): array
    {
        foreach ($categoriesMatrix as &$categoryItems) {
            if (isset($categoryItems[$sku])) {
                $categoryItems[$sku] = $categoryItems[$sku] - 1;
                if ($categoryItems[$sku] <= 0) {
                    unset($categoryItems[$sku]);
                }
            }
        }

        return $categoriesMatrix;
    }

    /**
     * Initialize category matrix where columns are categories, rows are items from cart
     *
     * @param array $categories
     * @param array $itemsForSet
     *
     * @return array
     */
    private function getCategoriesMatrix($categories, $itemsForSet)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $catsCollection */
        $catsCollection = $this->categoriesCollection->create();
        $catsCollection->addIdFilter($categories);
        $catsCollection->setOrder('level', $catsCollection::SORT_ORDER_DESC);

        $catsMatrix = [];
        $categoriesArray = $catsCollection->getData();

        foreach ($categoriesArray as $item) {
            $catsMatrix[$item['entity_id']] = [];
        }

        foreach ($itemsForSet as $item) {
            $productCategories = $item->getProduct()->getCategoryIds();

            foreach ($productCategories as $category) {
                $category = (int) $category;

                if (isset($catsMatrix[$category][$item->getProduct()->getData('sku')])) {
                    $catsMatrix[$category][$item->getProduct()->getData('sku')]++;
                    continue;
                }

                if (isset($catsMatrix[$category])) {
                    $catsMatrix[$category][$item->getProduct()->getData('sku')] = 1;
                }
            }
        }

        return $catsMatrix;
    }

    protected function getProductId(AbstractQuoteItem $item): string
    {
        if ($item->getProduct()->getTypeId() == Configurable::TYPE_CODE) {
            return (string)$item->getChildren()[0]->getProductId();
        } else {
            return (string)$item->getProductId();
        }
    }

    protected function getUniqueProductIdentifier(AbstractQuoteItem $item): string
    {
        $result = $this->getProductId($item);
        if ($item->getOptions()) {
            foreach ($item->getOptions() as $option) {
                $result .= '_' . $option->getId();
            }
        }

        return $result;
    }
}
