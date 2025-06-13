<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Observer;

use Magento\Framework\Event\ObserverInterface;

class CategoryFlatLoadNodesBefore implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Zend_Db_Select_Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var \Zend_Db_Select $select
         */
        $select = $observer->getEvent()->getSelect();
        $select->columns('main_table.thumbnail');
    }
}
