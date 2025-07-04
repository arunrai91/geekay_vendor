<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Helper;

use Amasty\Shopby\Model\Layer\Filter\Category;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\Manager;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class PermissionHelper extends AbstractHelper
{
    public const CUSTOMER_GROUPS = 'catalog/magento_catalogpermissions/grant_catalog_category_view_groups';

    public const FOR_SPECIFIED_CUSTOMER_GROUP = 2;

    public const PERMISSIONS_ENABLED = 'catalog/magento_catalogpermissions/enabled';

    public const CATALOG_PERMISSIONS = 'catalog/magento_catalogpermissions/grant_catalog_category_view';

    public const DENY_PERMISSION = '-2';

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Amasty\ShopbyBase\Model\Di\Wrapper
     */
    private $permissionModel;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Amasty\ShopbyBase\Model\Di\Wrapper $permissionModel,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->permissionModel = $permissionModel;
        $this->moduleManager = $context->getModuleManager();
        $this->storeManager = $storeManager;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function checkPermissions()
    {
        $isAllowed = true;
        if ($this->moduleManager->isEnabled('Magento_CatalogPermissions')
            && $this->scopeConfig->isSetFlag(self::PERMISSIONS_ENABLED, ScopeInterface::SCOPE_STORE)
        ) {
            $permissions = $this->getPermissions();
            $permissions = $permissions ? array_shift($permissions) : false;

            $isAllowed = $permissions
                && isset($permissions['grant_catalog_category_view'])
                && $permissions['grant_catalog_category_view'] !== self::DENY_PERMISSION;
            $isAllowed = $isAllowed || ($permissions ? false : $this->isAllowedPermissions());
        }

        return $isAllowed;
    }

    /**
     * @return array
     */
    public function getPermissions()
    {
        $store = $this->storeManager->getStore();
        return $this->permissionModel->getIndexForCategory(
            $store->getRootCategoryId(),
            $this->customerSession->getCustomerGroupId(),
            $store->getWebsiteId()
        );
    }

    /**
     * @return bool|mixed
     */
    private function isAllowedPermissions()
    {
        $allowCategories = $this->getCatalogCategoryPermissions();
        if ($allowCategories == self::FOR_SPECIFIED_CUSTOMER_GROUP) {
            $customerGroupId = $this->getCustomerGroupId();
            $allowedCustomerGroups = $this->getCustomerGroupPermissions();
            $allowCategories = in_array($customerGroupId, $allowedCustomerGroups);
        }

        return $allowCategories;
    }

    /**
     * @return int
     */
    public function getCustomerGroupId()
    {
        return $this->customerSession->isLoggedIn()
            ? $this->customerSession->getCustomer()->getGroupId()
            : 0;
    }

    /**
     * @return mixed
     */
    public function getCatalogCategoryPermissions()
    {
        return $this->scopeConfig->getValue(self::CATALOG_PERMISSIONS, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return array
     */
    public function getCustomerGroupPermissions()
    {
        $groups = $this->scopeConfig->getValue(self::CUSTOMER_GROUPS, ScopeInterface::SCOPE_STORE);
        $groups = explode(',', (string) $groups);

        return $groups;
    }
}
