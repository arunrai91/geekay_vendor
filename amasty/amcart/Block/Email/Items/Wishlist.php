<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Block\Email\Items;

use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Template;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\WishlistFactory;

class Wishlist extends Template
{
    /**
     * @var WishlistFactory
     */
    private $wishlistFactory;

    public function __construct(
        Template\Context $context,
        WishlistFactory $wishlistFactory,
        array $data = []
    ) {
        $this->wishlistFactory = $wishlistFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getItems()
    {
        $wishlistProducts = [];
        $customerId = $this->getQuoteCustomerId();

        if (empty($customerId)) {
            return $wishlistProducts;
        }

        $wishlist = $this->wishlistFactory->create()->loadByCustomerId($customerId);

        $wishlistItems = $wishlist->getItemCollection()->getItems();

        /** @var Item $wishlistItem */
        foreach ($wishlistItems as $wishlistItem) {
            /** @var Product $product */
            $product = $wishlistItem->getProduct();
            $wishlistProducts[] = $product;
        }

        return $wishlistProducts;
    }
}
