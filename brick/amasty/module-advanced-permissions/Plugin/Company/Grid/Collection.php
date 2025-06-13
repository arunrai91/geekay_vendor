<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Plugin\Company\Grid;

class Collection
{
    /**
     * Verification whether the method beforeLoad should be called
     *
     * @param \Magento\SharedCatalog\Plugin\Company\Model\ResourceModel\Company\Grid\CollectionPlugin $subject
     * @param callable $proceed
     * @param \Magento\Company\Model\ResourceModel\Company\Grid\Collection $collection
     * @param bool $printQuery [optional]
     * @param bool $logQuery [optional]
     *
     * @return array
     */
    public function aroundBeforeLoad(
        \Magento\SharedCatalog\Plugin\Company\Model\ResourceModel\Company\Grid\CollectionPlugin $subject,
        \Closure $proceed,
        \Magento\Company\Model\ResourceModel\Company\Grid\Collection $collection,
        $printQuery = false,
        $logQuery = false
    ) {
        $fromPart = $collection->getSelect()->getPart(\Magento\Framework\DB\Select::FROM);
        if (!isset($fromPart['customer_group'])) {
            return $proceed($collection, $printQuery, $logQuery);
        }

        return [$collection, $printQuery, $logQuery];
    }
}
