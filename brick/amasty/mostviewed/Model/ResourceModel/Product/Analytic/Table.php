<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\ResourceModel\Product\Analytic;

class Table
{
    public const TABLE_NAME = 'amasty_mostviewed_product_sales';

    public const ID_COLUMN = 'id';
    public const ORDER_ID_COLUMN = 'order_id';
    public const ORDER_INCREMENT_COLUMN = 'order_increment_id';
    public const RULE_ID_COLUMN = 'rule_id';
    public const RULE_NAME_COLUMN = 'rule_name';
    public const PRODUCT_NAME_COLUMN = 'product_name';
    public const PRODUCT_PRICE_COLUMN = 'product_price';
    public const PRODUCT_QTY_COLUMN = 'product_qty';
    public const BASE_GRAND_TOTAL_COLUMN = 'base_grand_total';
    public const GRAND_TOTAL_COLUMN = 'grand_total';
    public const BASE_CURRENCY_CODE_COLUMN = 'base_currency_code';
    public const ORDER_CURRENCY_CODE_COLUMN = 'order_currency_code';
    public const CREATED_AT_COLUMN = 'created_at';
}
