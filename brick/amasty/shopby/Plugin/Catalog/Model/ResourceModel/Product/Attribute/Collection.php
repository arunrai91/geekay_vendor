<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Catalog\Model\ResourceModel\Product\Attribute;

use \Amasty\Shopby\Model\Search\RequestGenerator;

class Collection
{
    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $subject
     * @param $column
     * @param $value
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormatParameter)
     */
    public function beforeGetItemByColumnValue(
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $subject,
        $column,
        $value
    ) {
        if ($column == 'attribute_code'
            && ($pos = strpos($value, RequestGenerator::FAKE_SUFFIX)) !== false
        ) {
            $value = substr($value, 0, $pos);
        }

        return [$column, $value];
    }
}
