<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Model\Rule;

use Amasty\Rules\Model\ResourceModel\Product\CatalogPriceRule;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Quote\Api\Data\CartInterface;

class QuoteStorage
{
    /**
     * @var array
     */
    private $productsInCart = [];

    /**
     * @var array
     */
    private $productsWithDiscount = [];

    /**
     * @var CatalogPriceRule
     */
    private $catalogPriceRule;

    public function __construct(
        CatalogPriceRule $catalogPriceRule
    ) {
        $this->catalogPriceRule = $catalogPriceRule;
    }

    private function setProductsInCart(CartInterface $quote): void
    {
        $productIds = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            if ($product->getTypeId() === Configurable::TYPE_CODE) {
                $product = current($item->getChildren())->getProduct();
            }

            $productIds[] = $product->getId();
        }
        $this->productsInCart[$quote->getId()] = array_unique($productIds);
    }

    private function getProductsInCart(CartInterface $quote): array
    {
        $quoteId = $quote->getId();
        if (!array_key_exists($quoteId, $this->productsInCart)) {
            $this->setProductsInCart($quote);
        }

        return $this->productsInCart[$quoteId];
    }

    public function getProductsWithDiscount(CartInterface $quote): array
    {
        $quoteId = $quote->getId();
        if (!array_key_exists($quoteId, $this->productsInCart)) {
            $productsInCart = $this->getProductsInCart($quote);
            $this->productsWithDiscount[$quoteId] = $this->catalogPriceRule->getCatalogRuleForProducts(
                $productsInCart,
                (int)$quote->getStore()->getWebsiteId(),
                (int)$quote->getCustomer()->getGroupId()
            );
        }

        return $this->productsWithDiscount[$quoteId];
    }
}
