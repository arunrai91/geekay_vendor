<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\ViewModel\Navigation;

use Magento\Catalog\Block\Product\ProductList\Toolbar as ToolbarBlock;
use Magento\Framework\Registry;

class Toolbar
{
    private const PRODUCT_LISTING_SEARCH_BLOCK = 'search.result';
    private const PRODUCT_LISTING_TOOLBAR_BLOCK = 'product_list_toolbar';
    private const SEARCH_SORTING = 'amsorting_search';
    
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param Registry $registry Used Deprecated class because of legacy compatibility.
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Is current layout are Search then prepare the product list toolbar.
     */
    public function resolveSearchLayoutToolbar(\Magento\Framework\View\LayoutInterface $layout): void
    {
        // determine is it a search result layout
        if (!$layout->getBlock(self::PRODUCT_LISTING_SEARCH_BLOCK)) {
            return;
        }
        $toolbarBlock = $layout->getBlock(self::PRODUCT_LISTING_TOOLBAR_BLOCK);

        if ($toolbarBlock && $toolbarBlock instanceof ToolbarBlock) {
            $this->prepareSearchToolbar($toolbarBlock);
        }
    }

    /**
     * Replace position order by relevance.
     */
    private function prepareSearchToolbar(ToolbarBlock $toolbarBlock): void
    {
        $toolbarBlock->setData('_current_grid_order', null);
        $toolbarBlock->setData('_current_grid_direction', null);
        $orders = $toolbarBlock->getAvailableOrders();
        unset($orders['position']);
        $orders['relevance'] = __('Relevance');
        $toolbarBlock->setAvailableOrders(
            $orders
        )->setDefaultDirection(
            'desc'
        );

        if (!$this->isAdvancedSorting()) {
            $toolbarBlock->setDefaultOrder('relevance');
        }
    }

    /**
     * Is Advanced Sorting are applied to toolbar collection.
     *
     * Advanced Sorting - provided by extension Amasty_Sorting
     */
    public function isAdvancedSorting(): bool
    {
        return (bool) $this->registry->registry(self::SEARCH_SORTING);
    }
}
