<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Api\Data;

interface RuleInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    public const ID = 'id';
    public const ROLE_ID = 'role_id';
    public const LIMIT_ORDERS = 'limit_orders';
    public const LIMIT_INVOICES = 'limit_invoices';
    public const LIMIT_SHIPMENTS = 'limit_shipments';
    public const LIMIT_MEMOS = 'limit_memos';
    public const PRODUCT_ACCESS_MODE = 'product_access_mode';
    public const CATEGORY_ACCESS_MODE = 'category_access_mode';
    public const SCOPE_ACCESS_MODE = 'scope_access_mode';
    public const ATTRIBUTE_ACCESS_MODE = 'attribute_access_mode';
    public const LIMIT_PGRID_EXTRA = 'limit_pgrid_extra';
    public const LIMIT_PRODUCT_SOURCES_MANAGEMENT = 'limit_product_sources_management';
    public const ROLE_ACCESS_MODE = 'role_access_mode';
    /**#@-*/

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return \Amasty\Rolepermissions\Api\Data\RuleInterface
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getRoleId();

    /**
     * @param int $roleId
     *
     * @return \Amasty\Rolepermissions\Api\Data\RuleInterface
     */
    public function setRoleId($roleId);

    /**
     * @return int
     */
    public function getLimitOrders();

    /**
     * @param int $limitOrders
     *
     * @return \Amasty\Rolepermissions\Api\Data\RuleInterface
     */
    public function setLimitOrders($limitOrders);

    /**
     * @return int
     */
    public function getLimitInvoices();

    /**
     * @param int $limitInvoices
     *
     * @return \Amasty\Rolepermissions\Api\Data\RuleInterface
     */
    public function setLimitInvoices($limitInvoices);

    /**
     * @return int
     */
    public function getLimitShipments();

    /**
     * @param int $limitShipments
     *
     * @return \Amasty\Rolepermissions\Api\Data\RuleInterface
     */
    public function setLimitShipments($limitShipments);

    /**
     * @return int
     */
    public function getLimitMemos();

    /**
     * @param int $limitMemos
     *
     * @return \Amasty\Rolepermissions\Api\Data\RuleInterface
     */
    public function setLimitMemos($limitMemos);

    /**
     * @return int
     */
    public function getProductAccessMode();

    /**
     * @param int $productAccessMode
     *
     * @return \Amasty\Rolepermissions\Api\Data\RuleInterface
     */
    public function setProductAccessMode($productAccessMode);

    /**
     * @return int
     */
    public function getCategoryAccessMode();

    /**
     * @param int $categoryAccessMode
     *
     * @return \Amasty\Rolepermissions\Api\Data\RuleInterface
     */
    public function setCategoryAccessMode($categoryAccessMode);

    /**
     * @return int
     */
    public function getScopeAccessMode();

    /**
     * @param int $scopeAccessMode
     *
     * @return \Amasty\Rolepermissions\Api\Data\RuleInterface
     */
    public function setScopeAccessMode($scopeAccessMode);

    /**
     * @return int
     */
    public function getAttributeAccessMode();

    /**
     * @param int $attributeAccessMode
     *
     * @return \Amasty\Rolepermissions\Api\Data\RuleInterface
     */
    public function setAttributeAccessMode($attributeAccessMode);

    /**
     * @return int
     */
    public function getLimitPgridExtra(): int;

    /**
     * @param int $isLimitPgridExtra
     * @return \Amasty\Rolepermissions\Api\Data\RuleInterface
     */
    public function setLimitPgridExtra(int $isLimitPgridExtra): RuleInterface;

    /**
     * @return int
     */
    public function getLimitProductSourcesManagement(): int;

    /**
     * @param int $isLimitProductSourcesManagement
     *
     * @return \Amasty\Rolepermissions\Api\Data\RuleInterface
     */
    public function setLimitProductSourcesManagement(int $isLimitProductSourcesManagement): RuleInterface;

    /**
     * @return int
     */
    public function getRoleAccessMode();

    /**
     * @param int $roleAccessMode
     *
     * @return \Amasty\Rolepermissions\Api\Data\RuleInterface
     */
    public function setRoleAccessMode($roleAccessMode);
}
