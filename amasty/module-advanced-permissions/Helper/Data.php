<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Helper;

use Amasty\Rolepermissions\Block\Adminhtml\Role\Tab\Attributes;
use Amasty\Rolepermissions\Model\Authorization\GetCurrentUserInterface;
use Amasty\Rolepermissions\Model\RuleFactory;
use Amasty\Rolepermissions\Plugin\StoreManager;
use Magento\Backend\Model\UrlInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollectionFactory;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManager as MagentoStoreManager;

class Data extends AbstractHelper
{
    public const ALLOWED_AREA_CODES = [
        Area::AREA_ADMINHTML,
        Area::AREA_WEBAPI_REST,
    ];

    /**
     * @var null
     */
    protected $skipObjectRestriction = null;

    /**
     * @var array
     */
    protected $restrictedAttributeIds = [];

    /**
     * @var array
     */
    protected $restrictedAttrSetIds = [];

    /**
     * @var array
     */
    protected $allowedSetIds = [];

    /**
     * @var array
     */
    protected $allowedAttCodes = [];

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var UrlInterface
     */
    protected $backendUrl;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var AttributeCollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var AttributeSetCollectionFactory
     */
    protected $attrSetCollectionFactory;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var Category
     */
    protected $category;

    /**
     * @var MagentoStoreManager
     */
    private $storeManager;

    /**
     * @var null|int[]
     */
    private $allStoresOrWebsites;

    /**
     * @var ActionFlag
     */
    private $actionFlag;

    /**
     * @var GetCurrentUserInterface
     */
    private $getCurrentUser;

    public function __construct(
        Context $context,
        Registry $registry,
        GetCurrentUserInterface $getCurrentUser,
        ManagerInterface $messageManager,
        UrlInterface $backendUrl,
        ResponseInterface $response,
        AttributeCollectionFactory $collectionFactory,
        AttributeSetCollectionFactory $attrSetCollectionFactory,
        ProductFactory $productFactory,
        RuleFactory $ruleFactory,
        CategoryFactory $categoryFactory,
        MagentoStoreManager $storeManager,
        ActionFlag $actionFlag
    ) {
        $this->coreRegistry = $registry;
        $this->getCurrentUser = $getCurrentUser;
        $this->messageManager = $messageManager;
        $this->backendUrl = $backendUrl;
        $this->response = $response;
        $this->collectionFactory = $collectionFactory;
        $this->attrSetCollectionFactory = $attrSetCollectionFactory;
        $this->productFactory = $productFactory;
        $this->ruleFactory = $ruleFactory;
        $this->categoryFactory = $categoryFactory;
        $this->storeManager = $storeManager;
        $this->actionFlag = $actionFlag;
        parent::__construct($context);
    }

    /**
     * @param $ruleCategoryIds
     * @return mixed
     */
    public function getParentCategoriesIds($ruleCategoryIds)
    {
        foreach ($ruleCategoryIds as $categoryId) {
            $parentCategories = $this->getParentIds($categoryId);

            if ($parentCategories) {
                foreach ($parentCategories as $parentId) {
                    if (!in_array($parentId, $ruleCategoryIds)) {
                        array_push($ruleCategoryIds, $parentId);
                    }
                }
            }
        }

        return $ruleCategoryIds;
    }

    /**
     * Get all parent categories ids
     *
     * @return array
     */
    public function getParentIds($categoryId = false)
    {
        if (!$categoryId || $this->category && $this->category->getId() == $categoryId) {
            return $this->category->getParentIds();
        } else {
            return $this->getCategory($categoryId)->getParentIds();
        }
    }

    /**
     * Get category object
     * Using $_categoryFactory
     *
     * @return Category
     */
    public function getCategory($categoryId)
    {
        $this->category = $this->categoryFactory->create();
        $this->category->load($categoryId);

        return $this->category;
    }

    /**
     * @return \Amasty\Rolepermissions\Model\Rule | bool
     */
    public function currentRule()
    {
        if (($rule = $this->coreRegistry->registry('current_amrolepermissions_rule')) == null) {
            $user = $this->getCurrentUser->execute();
            if (!$user) {
                return false;
            }

            $rule = $this->ruleFactory->create()->loadByRole($user->getRole()->getId());
            $this->coreRegistry->register('current_amrolepermissions_rule', $rule, true);
        }

        return $rule;
    }

    public function redirectHome()
    {
        if (!$this->getCurrentUser->execute()) {
            return;
        }

        $this->messageManager->addError(__('Access Denied'));

        if ($this->_request->getActionName() == 'index') {
            $page = $this->backendUrl->getStartupPageUrl();
            $url = $this->backendUrl->getUrl($page);
        } else {
            $url = $this->backendUrl->getUrl('*/*');
        }

        /** @see \Magento\Framework\App\FrontController::getActionResponse */
        $this->actionFlag->set('', ActionInterface::FLAG_NO_DISPATCH, true);

        $this->response
            ->setRedirect($url)
            ->sendResponse();
    }

    public function restrictObjectByStores($data)
    {
        [$name, $value, $isWebsite] = $this->_getRelationField($data);

        if ($value) {
            $rule = $this->currentRule();

            if ($isWebsite) {
                $allowedIds = $rule->getPartiallyAccessibleWebsites();
            } else {
                $allowedIds = $rule->getScopeStoreviews();
            }

            if (!is_array($value)) {
                $value = explode(',', $value);
            }

            if (($value != [0]) && !array_intersect($value, $allowedIds)) {
                $this->redirectHome();
            }
        }

        return $this;
    }

    public function alterObjectStores($object)
    {
        [$name, $value, $isWebsite] = $this->_getRelationField($object->getData());

        if ($value || $isWebsite) {
            if (!is_array($value)) {
                $value = explode(',', $value);
                $array = false;
            } else {
                $array = true;
            }

            if ($object->getId()) {
                [$origName, $origValue, $isWebsite] = $this->_getRelationField($object->getOrigData());

                if ($origName === null) {
                    $oldObject = clone $object;
                    $oldObject->load($object->getId());

                    [$origName, $origValue, $isWebsite] = $this->_getRelationField($oldObject->getOrigData());
                }

                if ($origName === null) {
                    $origName = [];
                    $origValue = [];
                } elseif (!is_array($origValue)) {
                    $origValue = explode(',', $origValue);
                }
            } else {
                $origName = [];
                $origValue = [];
            }

            if ($value != $origValue) {
                $rule = $this->currentRule();

                if ($isWebsite) {
                    $allowedIds = $rule->getPartiallyAccessibleWebsites();
                } else {
                    $allowedIds = $rule->getScopeStoreviews();
                }

                $newValue = $this->combine($origValue, $value, $allowedIds);

                if ($newValue && $newValue == $this->allStoresOrWebsites) {
                    $newValue = ["0"];
                }

                if (!$array) {
                    $newValue = implode(',', array_filter($newValue));
                }

                if ($name === $origName) {
                    $object->setData($name, $newValue);
                }
            }
        }

        return $this;
    }

    /**
     * @param array $old
     * @param array $new
     * @param array $allowed
     *
     * @return array
     */
    public function combine($old, $new, $allowed)
    {
        if (!is_array($old)) {
            $old = [];
        }

        $map = array_flip(array_unique(array_merge($new, $old)));

        if ($allowed) {
            foreach ($map as $id => $order) {
                if (in_array($id, $allowed)) {
                    if (!in_array($id, $new)) {
                        unset($map[$id]);
                    }
                } else {
                    if (!in_array($id, $old)) {
                        unset($map[$id]);
                    }
                }
            }
        }

        return array_keys($map);
    }

    protected function _getRelationField($data)
    {
        if (!$data) {
            return false;
        }

        $fieldNames = [
            'website_id', 'website_ids', 'websites',
            'store_id', 'store_ids', 'stores'
        ];

        foreach ($fieldNames as $name) {
            if (isset($data[$name])) {
                if (substr($name, 0, 7) == 'website') {
                    $isWebsite = true;
                } else {
                    $isWebsite = false;
                }

                $scopeIds = $data[$name];
                $isArray = true;

                if (!is_array($scopeIds)) {
                    $scopeIds = explode(',', (string)$scopeIds);
                    $isArray = false;
                }

                if (array_search("0", $scopeIds) !== false) {
                    $this->coreRegistry->register(StoreManager::AM_SKIP_STORES_PLUGIN, true, true);

                    if ($isWebsite) {
                        $scopeIds = array_keys($this->storeManager->getWebsites());
                    } else {
                        $scopeIds = array_keys($this->storeManager->getStores());
                    }

                    $this->allStoresOrWebsites = $scopeIds;
                    $this->coreRegistry->unregister(StoreManager::AM_SKIP_STORES_PLUGIN);
                }

                if (!$isArray) {
                    $scopeIds = implode(',', $scopeIds);
                }

                return [$name, $scopeIds, $isWebsite];
            }
        }
    }

    public function canSkipObjectRestriction()
    {
        if ($this->skipObjectRestriction === null) {
            $this->skipObjectRestriction = false;
            $action = $this->_request->getActionName();

            if (in_array($action, ['edit', 'view', 'index', 'render'])) {
                $controller = $this->_request->getControllerName();
                $rule = $this->coreRegistry->registry('current_amrolepermissions_rule');

                if ((!$rule->getLimitOrders()
                        && ($controller == 'order' || ($this->_request->getParam('namespace') == 'sales_order_grid')))
                    ||
                    (!$rule->getLimitInvoices()
                        && ($controller == 'order_invoice' || $controller == 'order_transactions'))
                    ||
                    (!$rule->getLimitShipments() && $controller == 'order_shipment')
                    ||
                    (!$rule->getLimitMemos() && $controller == 'order_creditmemo')
                ) {
                    $this->skipObjectRestriction = true;
                }
            }
        }

        return $this->skipObjectRestriction;
    }

    public function getAllowedAttributeCodes()
    {
        if (empty($this->allowedAttCodes)) {
            /** @var \Amasty\Rolepermissions\Model\ResourceModel\Rule $rule */
            $rule = $this->currentRule();

            if (is_object($rule)) {
                if (Attributes::MODE_SELECTED == $rule->getAttributeAccessMode()) {
                    $allowedAttributeIds = $rule->getAttributes();
                    $collectionFactory = $this->collectionFactory->create();
                    $collectionFactory->addFieldToFilter('main_table.attribute_id', ['in' => $allowedAttributeIds]);
                    $this->allowedAttCodes = $collectionFactory->getColumnValues('attribute_code');
                } else {
                    $this->allowedAttCodes = true;
                }
            }
        }

        return $this->allowedAttCodes;
    }

    public function getRestrictedAttributeIds(): array
    {
        if (empty($this->restrictedAttributeIds)) {
            /** @var \Amasty\Rolepermissions\Model\ResourceModel\Rule $rule */
            $rule = $this->currentRule();

            if (is_object($rule) && $allowedAttributeIds = $rule->getAttributes()) {
                $collectionFactory = $this->collectionFactory->create();
                // without this line we get only allowed attribute ids instead of all
                $this->coreRegistry->register('its_amrolepermissions', 1, true);
                $allAttributeIds = $collectionFactory->addVisibleFilter()->getColumnValues('attribute_id');
                $this->coreRegistry->unregister('its_amrolepermissions');
                $this->restrictedAttributeIds = array_diff($allAttributeIds, $allowedAttributeIds);
            }
        }

        return $this->restrictedAttributeIds;
    }

    public function getRestrictedAttributeCodes(): array
    {
        $restrictedAttributeIds = $this->getRestrictedAttributeIds();
        $restrictedAttributeCodes = [];

        if ($restrictedAttributeIds) {
            $collection = $this->collectionFactory->create();
            // without this line we get only allowed attribute ids instead of all
            $this->coreRegistry->register('its_amrolepermissions', 1, true);
            $restrictedAttributeCodes = $collection->addFieldToFilter(
                'main_table.attribute_id',
                $restrictedAttributeIds
            )->getColumnValues('attribute_code');
            $this->coreRegistry->unregister('its_amrolepermissions');
        }

        return $restrictedAttributeCodes;
    }

    public function getRestrictedSetIds()
    {
        if (empty($this->restrictedAttrSetIds)) {
            $restrictedAttributeIds = $this->getRestrictedAttributeIds();

            if ($restrictedAttributeIds) {
                /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $collection */
                $collection = $this->attrSetCollectionFactory->create();
                $connection = $collection->getConnection();
                $select = $connection->select()->distinct(
                    true
                )->from(
                    $collection->getTable('eav_entity_attribute'),
                    'attribute_set_id'
                )->where(
                    'attribute_id IN(?)',
                    $restrictedAttributeIds
                );
                $this->restrictedAttrSetIds = $connection->fetchCol($select);
            }
        }

        return $this->restrictedAttrSetIds;
    }

    public function getAllowedSetIds()
    {
        if (empty($this->allowedSetIds)) {
            $restrictedSetIds = $this->getRestrictedSetIds();
            /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $collection */
            $collection = $this->attrSetCollectionFactory->create();

            if (!empty($restrictedSetIds)) {
                $collection
                    ->distinct(true)
                    ->addFieldToFilter(
                        'entity_type_id',
                        ['eq' => $this->productFactory->create()->getResource()->getTypeId()]
                    )
                    ->addFieldToFilter('attribute_set_id', ['nin' => $restrictedSetIds]);
            }

            $this->allowedSetIds = $collection->getConnection()->fetchCol($collection->getSelect());
        }

        return $this->allowedSetIds;
    }

    public function restrictAttributeSets()
    {
        $restrict = true;
        $collectionSize = $this->attrSetCollectionFactory->create()->addFieldToFilter(
            'entity_type_id',
            ['eq' => $this->productFactory->create()->getResource()->getTypeId()]
        )->getSize();

        if ($collectionSize > 0) {
            $restrict = false;
        }

        return $restrict;
    }
}
