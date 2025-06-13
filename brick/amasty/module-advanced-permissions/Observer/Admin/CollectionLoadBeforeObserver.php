<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Observer\Admin;

use Amasty\Customform\Model\ResourceModel\AbstractPageCollection as AmCustomformAbstractPageCollection;
use Amasty\Faq\Model\ResourceModel\Category\Collection as AmFaqCategoryCollection;
use Amasty\Faq\Model\ResourceModel\Question\Collection as AmFaqQuestionCollection;
use Amasty\Faq\Model\ResourceModel\VisitStat\Collection as AmFaqVisitStatCollection;
use Amasty\GdprCookie\Model\ResourceModel\CookieConsent\Collection as AmGdprCookieCollection;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\Collection as AmGiftCardAccountCollection;
use Amasty\Groupcat\Model\ResourceModel\Rule\Collection as AmGroupcatCollection;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Collection as AmMultiInventoryCollection;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Grid\Collection as AmMultiInventoryGridCollection;
use Amasty\Orderarchive\Model\ResourceModel\OrderGrid\Collection as AmOrderarchiveCollection;
use Amasty\RequestQuote\Model\ResourceModel\Quote\Grid\Collection as AmRequestQuoteCollection;
use Amasty\Rolepermissions\Api\Data\RuleInterface;
use Amasty\Rolepermissions\Helper\Data;
use Amasty\Shiprules\Model\ResourceModel\Rule\Collection as AmShiprulesCollection;
use Magento\Catalog\Model\Category;
use Magento\Customer\Model\ResourceModel\Customer\Collection as CustomerCollection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Grid\Collection as SalesCollection;
use Magento\SalesArchive\Model\ResourceModel\Order\Collection as SalesArchiveCollection;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;
use Zend_Db_Select_Exception;

class CollectionLoadBeforeObserver implements ObserverInterface
{
    public const STORES_KEY = 'stores';

    public const STORE_ID_KEY = 'store_id';

    public const STORE_IDS_KEY = 'store_ids';

    public const WEBSITE_KEY = 'website';

    public const WEBSITE_ID_KEY = 'website_id';

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var Store
     */
    private $store;

    /**
     * @var Website
     */
    private $website;

    public function __construct(
        Registry $registry,
        Data $helper,
        ModuleManager $moduleManager,
        Website $website,
        Store $store
    ) {
        $this->registry = $registry;
        $this->helper = $helper;
        $this->moduleManager = $moduleManager;
        $this->website = $website;
        $this->store = $store;
    }

    /**
     * @param Observer $observer
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws Zend_Db_Select_Exception
     */
    public function execute(Observer $observer)
    {
        /** @var \Amasty\Rolepermissions\Model\Rule $rule */
        $rule = $this->registry->registry('current_amrolepermissions_rule');

        if (!$rule) {
            return;
        }

        $collection = $observer->getCollection();

        if ($rule->getScopeStoreviews()) {
            if ($this->isCollectionHasStoreFilterWithParam($collection)) {
                $collection->addStoreFilter($rule->getScopeStoreviews(), false);
            }

            if ($this->isCollectionHasStoreFilter($collection)) {
                $collection->addStoreFilter($rule->getScopeStoreviews());
            }

            if ($this->isCollectionHasStoreIdField($collection)) {
                $this->addStoreFilter($collection, $rule);
            }

            if ($this->isCollectionHasWebsiteField($collection)) {
                $this->addWebsiteFilter($collection, $rule, self::WEBSITE_KEY);
            }

            if ($this->isCollectionHasWebsiteIdField($collection)) {
                $this->addWebsiteFilter($collection, $rule, self::WEBSITE_ID_KEY);
            }

            if ($this->isCollectionHasSetStoreIdsMethod($collection)) {
                $collection->setStoreIds($rule->getScopeStoreviews());
            }

            if ($this->isResourceModelHasStoreIdField($collection)) {
                $this->addStoreFilter($collection, $rule);
            }

            if ($collection instanceof \Magento\Review\Model\ResourceModel\Review\Product\Collection) {
                $this->addStoreFilterToReview($collection, $rule);
            }

            if ($collection instanceof \Magento\Customer\Model\ResourceModel\Grid\Collection) {
                $this->addCustomerFilter($collection, $rule);
            }

            if ($collection instanceof SearchResult) {
                $this->addFilterToSearchResultCollection($collection, $rule);
            }

            if ($collection instanceof AmGroupcatCollection) { // @phpstan-ignore class.notFound
                $this->addStoreFilterToGroupcatRules($collection, $rule);
            }

            if ($collection instanceof AmMultiInventoryGridCollection // @phpstan-ignore class.notFound
                || $collection instanceof AmMultiInventoryCollection // @phpstan-ignore class.notFound
            ) {
                $this->addFilterToWarehouseCollection($collection, $rule);
            }
        }

        $this->addCategoryFilter($collection, $rule);
        $this->addAttributesFilter($collection, $rule);
    }

    /**
     * @param $collection
     * @param $rule
     *
     * @throws Zend_Db_Select_Exception
     */
    public function addStoreFilter($collection, $rule)
    {
        if ($collection instanceof \Magento\Store\Model\ResourceModel\Store\Collection
            || $collection instanceof \Magento\Store\Model\ResourceModel\Website\Collection
        ) {
            /** Sometimes we shouldn't change store collection to load default values from default stores */
            if ($this->registry->registry('am_dont_change_collection')) {
                $this->registry->unregister('am_dont_change_collection');

                return;
            }
        }

        if ($collection instanceof AmShiprulesCollection) { // @phpstan-ignore class.notFound
            $collection->addStoreFilter($rule->getScopeStoreviews());
        }

        /** Its unable to retrieve RequestQuote grid collection plain select, but it can be modified */
        if (!$collection->getSelect()->__toString()
            && !$collection instanceof AmRequestQuoteCollection // @phpstan-ignore class.notFound
        ) {
            return;
        }

        $storeIdKey = self::STORE_ID_KEY;

        if ($collection instanceof AmFaqVisitStatCollection) { // @phpstan-ignore class.notFound
            $storeIdKey = self::STORE_IDS_KEY;
        }

        if (in_array($storeIdKey, $this->getCollectionKeys($collection))) {
            $select = $collection->getSelect();
            $alias = $this->getMainAlias($select) ? $this->getMainAlias($select) . '.' : '';
            $allowedStoreViews = $rule->getScopeStoreviews() ?: [];

            if ($this->isNeedSkipAlias($collection)) {
                $collection->addFieldToFilter($storeIdKey, ['in' => $allowedStoreViews]);
            } else {
                $collection->addFieldToFilter($alias . $storeIdKey, ['in' => $allowedStoreViews]);
            }
        }
    }

    private function addWebsiteFilter(AbstractCollection $collection, RuleInterface $rule, string $websiteKey): void
    {
        $allowedStoreIds = $rule->getScopeStoreviews() ?: [];
        $websiteIds = [];

        foreach ($allowedStoreIds as $storeId) {
            $websiteIds[] = $this->store->load($storeId)->getWebsiteId();
        }

        if (!empty($websiteIds)) {
            $select = $collection->getSelect();
            $alias = $this->getMainAlias($select) ? $this->getMainAlias($select) . '.' : '';

            $collection->addFieldToFilter($alias . $websiteKey, ['in' => $websiteIds]);
        }
    }

    /**
     * @param $collection
     * @param \Amasty\Rolepermissions\Model\Rule $rule
     */
    private function addCustomerFilter($collection, $rule)
    {
        if (!$collection->getSelect()->__toString()) {
            return;
        }

        if ($allowedWebsites = $rule->getScopeWebsites()) {
            $collection->addFieldToFilter('website_id', ['in' => $allowedWebsites]);
        } elseif ($allowedStoreViews = $rule->getScopeStoreviews()) {
            $collection->getSelect()->joinLeft(
                ['customer_data' => $collection->getTable('customer_entity')],
                'main_table.entity_id = customer_data.entity_id',
                []
            )->where('customer_data.store_id IN (' . implode(',', $allowedStoreViews) . ')');
        }
    }

    /**
     * @param $collection
     *
     * @return bool
     */
    private function isCollectionHasStoreIdField($collection)
    {
        return $collection instanceof \Magento\Wishlist\Model\ResourceModel\Item\Collection
            || $collection instanceof \Magento\Tax\Model\ResourceModel\Calculation\Rate\Title\Collection
            || $collection instanceof \Magento\Search\Model\ResourceModel\SynonymGroup\Collection
            || $collection instanceof \Magento\Sales\Model\ResourceModel\Order\Item\Collection
            || $collection instanceof \Magento\Sales\Model\ResourceModel\Order\Collection
            || $collection instanceof \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection
            || $collection instanceof \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection
            || $collection instanceof \Magento\Quote\Model\ResourceModel\Quote\Item\Collection
            || $collection instanceof \Magento\Quote\Model\ResourceModel\Quote\Collection
            || $collection instanceof \Magento\Paypal\Model\ResourceModel\Report\Settlement\Row\Collection
            || $collection instanceof \Magento\Paypal\Model\ResourceModel\Billing\Agreement\Collection
            || $collection instanceof \Magento\Widget\Model\ResourceModel\Layout\Link\Collection
            || $collection instanceof \Magento\Eav\Model\ResourceModel\Form\Type\Collection
            || $collection instanceof SalesCollection // @phpstan-ignore class.notFound
            || $collection instanceof \Magento\Sales\Model\ResourceModel\Order\Shipment\Grid\Collection
            || $collection instanceof \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Grid\Collection
            || $collection instanceof \Magento\Sales\Model\ResourceModel\Order\Grid\Collection
            || $collection instanceof \Magento\Customer\Model\ResourceModel\Online\Grid\Collection
            || $collection instanceof \Magento\Store\Model\ResourceModel\Website\Collection
            || $collection instanceof \Magento\Customer\Model\ResourceModel\Customer\Collection
            || $collection instanceof \Magento\Reports\Model\ResourceModel\Report\Collection\AbstractCollection
            || $collection instanceof AmShiprulesCollection // @phpstan-ignore class.notFound
            || $collection instanceof AmOrderarchiveCollection // @phpstan-ignore class.notFound
            || $collection instanceof AmFaqVisitStatCollection // @phpstan-ignore class.notFound
            || $collection instanceof SalesArchiveCollection; // @phpstan-ignore class.notFound
    }

    /**
     * @param AbstractCollection $collection
     * @return bool
     */
    private function isCollectionHasWebsiteField($collection): bool
    {
        return $collection instanceof AmGdprCookieCollection; // @phpstan-ignore class.notFound
    }

    private function isCollectionHasWebsiteIdField($collection): bool
    {
        return $collection instanceof AmGiftCardAccountCollection; // @phpstan-ignore class.notFound
    }

    /**
     * @param $collection
     *
     * @return bool
     */
    private function isCollectionHasStoreFilter($collection)
    {
        return $collection instanceof \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection
            || $collection instanceof \Magento\Sales\Model\ResourceModel\Sale\Collection
            || $collection instanceof \Magento\Review\Model\ResourceModel\Review\Collection
            || $collection instanceof \Magento\Reports\Model\ResourceModel\Quote\Item\Collection
            || $collection instanceof \Magento\Newsletter\Model\ResourceModel\Queue\Collection
            || $collection instanceof \Magento\Widget\Model\ResourceModel\Layout\Update\Collection
            || $collection instanceof \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\Collection
            || $collection instanceof \Magento\Catalog\Model\ResourceModel\Category\Flat\Collection
            || $collection instanceof \Magento\Cms\Model\ResourceModel\AbstractCollection
            || $collection instanceof \Magento\Reports\Model\ResourceModel\Event\Collection
            || $collection instanceof \Magento\Swatches\Model\ResourceModel\Swatch\Collection
            || $collection instanceof \Magento\Newsletter\Model\ResourceModel\Subscriber\Collection
            || $collection instanceof \Magento\Search\Model\ResourceModel\Query\Collection
            || $collection instanceof \Magento\Sitemap\Model\ResourceModel\Sitemap\Collection
            || $collection instanceof \Magento\Review\Model\ResourceModel\Review\Summary\Collection
            || $collection instanceof \Magento\Theme\Model\ResourceModel\Design\Collection
            || $collection instanceof AmCustomformAbstractPageCollection // @phpstan-ignore class.notFound
            || $collection instanceof AmFaqQuestionCollection // @phpstan-ignore class.notFound
            || $collection instanceof AmFaqCategoryCollection; // @phpstan-ignore class.notFound
    }

    /**
     * @param $collection
     *
     * @return bool
     */
    private function isCollectionHasStoreFilterWithParam($collection)
    {
        return $collection instanceof \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection
            || $collection instanceof \Magento\Widget\Model\ResourceModel\Widget\Instance\Collection
            || $collection instanceof \Magento\Cms\Model\ResourceModel\Block\Grid\Collection
            || $collection instanceof \Magento\Cms\Model\ResourceModel\Page\Grid\Collection;
    }

    /**
     * @param $collection
     *
     * @return bool
     */
    private function isCollectionHasSetStoreIdsMethod($collection)
    {
        return $collection instanceof \Magento\Reports\Model\ResourceModel\Accounts\Collection
            || $collection instanceof \Magento\Reports\Model\ResourceModel\Report\Collection;
    }

    /**
     * @param $collection
     *
     * @return bool
     */
    private function isResourceModelHasStoreIdField($collection)
    {
        $resourceModel = $collection->getResource();

        return $resourceModel instanceof \Magento\Theme\Model\ResourceModel\Design\Config
            || $resourceModel instanceof \Magento\Search\Model\ResourceModel\SynonymGroup;
    }

    private function isNeedSkipAlias(AbstractDb $collection): bool
    {
        return $collection instanceof CustomerCollection;
    }

    private function addFilterToSearchResultCollection(
        SearchResult $collection,
        RuleInterface $rule
    ): void {
        $allowedStoreIds = $rule->getScopeStoreviews();
        $select = $collection->getSelect();
        $alias = $this->getMainAlias($select) ? $this->getMainAlias($select) . '.' : '';

        $collectionKeys = $this->getCollectionKeys($collection);

        if (in_array(self::STORE_ID_KEY, $collectionKeys)) {
            $collection->addFieldToFilter($alias . self::STORE_ID_KEY, ['in' => $allowedStoreIds]);
        } elseif (in_array(self::STORES_KEY, $collectionKeys)) {
            $this->addStoreIdsFilter(
                $collection,
                $allowedStoreIds,
                $alias . self::STORES_KEY
            );
        }
    }

    private function addStoreIdsFilter(
        AbstractCollection $collection,
        array $allowedStoreIds,
        string $storesFieldName
    ): void {
        if (!empty($allowedStoreIds)) {
            $where = [];
            $allowedStoreIds[] = Store::DEFAULT_STORE_ID;

            foreach ($allowedStoreIds as $storeId) {
                $where[] = $collection->getConnection()->prepareSqlCondition(
                    $storesFieldName,
                    ['finset' => $storeId]
                );
            }

            $collection->getSelect()->where(implode(' OR ', $where));
        }
    }

    private function getCollectionKeys(AbstractDb $collection): array
    {
        return array_keys(
            $collection->getConnection()->describeTable($collection->getMainTable())
        );
    }

    /**
     * @param $collection
     * @param $rule
     */
    private function addFilterToWarehouseCollection($collection, $rule)
    {
        if ($collection->getFlag('am_warehouse_clone')) {
            return;
        }

        $allowedWarehouseIds = [];
        $allowedStoreViews = $rule->getScopeStoreviews();
        $collectionClone = clone $collection;
        $collectionClone->setFlag('am_warehouse_clone', true);

        $collectionClone->getSelect()
            ->reset(Select::COLUMNS)
            ->reset(Select::WHERE)
            ->columns(['warehouse_id'])
            ->group('main_table.warehouse_id')
            ->joinLeft(
                ['warehouse_store_table' => $collectionClone->getTable('amasty_multiinventory_store')],
                'main_table.warehouse_id = warehouse_store_table.warehouse_id',
                []
            )->where('warehouse_store_table.store_id IN (?)', implode(',', $allowedStoreViews));

        foreach ($collectionClone as $item) {
            $allowedWarehouseIds[] = $item->getId();
        }

        // keep access to default warehouse
        if (!in_array(1, $allowedWarehouseIds)) {
            $allowedWarehouseIds[] = 1;
        }

        $select = $collection->getSelect();
        $alias = $this->getMainAlias($select) ? $this->getMainAlias($select) . '.' : '';
        $collection->addFieldToFilter($alias . 'warehouse_id', ['in' => $allowedWarehouseIds]);
    }

    /**
     * @param $collection
     * @param $rule
     *
     * @throws Zend_Db_Select_Exception
     */
    private function addStoreFilterToGroupcatRules($collection, $rule)
    {
        $select = $collection->getSelect();
        $alias = $this->getMainAlias($select) ? $this->getMainAlias($select) . '.' : '';
        $allowedStoreViews = $rule->getScopeStoreviews();
        $where = $select->getPart(Select::WHERE);

        foreach ($where as $key => $condition) {
            if (strpos($condition, 'rule_id') !== false) {
                $where[$key] = str_replace('rule_id', $alias . 'rule_id', $condition);
            }
        }

        $collection->getSelect()->reset(Select::WHERE);
        $collection->getSelect()->joinLeft(
            ['rule_store' => $collection->getTable('amasty_groupcat_rule_store')],
            'main_table.rule_id = rule_store.rule_id',
            []
        )->where('rule_store.store_id IN (?)', implode(',', $allowedStoreViews));
    }

    /**
     * @param $collection
     * @param $rule
     */
    private function addStoreFilterToReview($collection, $rule)
    {
        $storesIds = $rule->getScopeStoreviews();

        foreach ($storesIds as $storesId) {
            $collection->addStoreFilter($storesId);
        }
    }

    /**
     * @param $collection
     * @param $rule
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addCategoryFilter($collection, $rule)
    {
        if ($collection instanceof \Magento\Catalog\Model\ResourceModel\Category\Collection) {
            $ruleCategories = $rule->getCategories();

            if ($ruleCategories) {
                $ruleCategories = $this->helper->getParentCategoriesIds($ruleCategories);
                $collection->addFieldToFilter('entity_id', ['in' => $ruleCategories]);
            } else {
                $rootCategories = [];
                /** Hide categories from another store */
                if ($rule->getScopeWebsites() || $rule->getScopeStoreviews()) {
                    $storeIds = $rule->getScopeStoreviews();

                    foreach ($storeIds as $storeId) {
                        /** @var \Magento\Store\Model\Store $store */
                        $store = $this->store->load($storeId);
                        if ($categoryRoot = $store->getRootCategoryId()) {
                            $rootCategories[] = $categoryRoot;
                        }
                    }
                }

                if ($rootCategories) {
                    $rootCategories = array_unique($rootCategories);
                    $allRootCategoryIds = $this->getRootCategoryIds($collection);
                    $deniedCategories = array_diff($allRootCategoryIds, $rootCategories);

                    if ($deniedCategories) {
                        $collection->getSelect()
                            ->where('e.entity_id NOT IN (?)', $deniedCategories);

                        foreach ($deniedCategories as $id) {
                            $collection->getSelect()->where('e.path NOT LIKE ?', '%/' . $id . '/%');
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $collection
     * @param \Amasty\Rolepermissions\Model\Rule $rule
     */
    public function addAttributesFilter($collection, $rule)
    {
        $ruleAttributes = $rule->getAttributes();

        if ($ruleAttributes && !$this->registry->registry('its_amrolepermissions')) {
            if ($collection instanceof \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection) {
                $collection->addFieldToFilter('main_table.attribute_id', ['in' => $ruleAttributes]);
            }
        }
    }

    /**
     * @param Select $select
     *
     * @return bool|int|string
     */
    protected function getMainAlias(Select $select)
    {
        try {
            $from = $select->getPart(Select::FROM);
        } catch (Zend_Db_Select_Exception $e) {
            return false;
        }

        foreach ($from as $alias => $data) {
            if ($data['joinType'] == 'from') {
                return $alias;
            }
        }

        return false;
    }

    /**
     * Get all root categories id
     *
     * @param $collection
     *
     * @return array
     */
    private function getRootCategoryIds($collection)
    {
        $connection = $collection->getConnection();
        $select = $connection->select()->from(
            $collection->getMainTable(),
            'entity_id'
        )->where('parent_id = ?', Category::TREE_ROOT_ID);

        return $connection->fetchCol($select);
    }
}
