<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Observer;

use Amasty\Shopby\Model\Request\IsAllProductsRegistry;
use Magento\Framework\Event\ObserverInterface;

class CategoryManagerInitAfter implements ObserverInterface
{
    /**
     * @var IsAllProductsRegistry
     */
    private IsAllProductsRegistry $isAllProductsRegistry;

    public function __construct(IsAllProductsRegistry $isAllProductsRegistry)
    {
        $this->isAllProductsRegistry = $isAllProductsRegistry;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->isAllProductsRegistry->setIsAllProducts(true);
    }
}
