<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Api;

use Amasty\Mostviewed\Api\CartGroupResolverInterface;
use Amasty\Mostviewed\Api\Data\GroupProductsResultInterface;
use Amasty\Mostviewed\Model\Api\Group\GenerateEmptyGroupProductsResult;
use Amasty\Mostviewed\Model\Api\Group\Quote\GetLastAddedProduct;
use Amasty\Mostviewed\Model\Customer\CustomerGroupContextInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\GuestCartRepositoryInterface;

class CartGroupResolver implements CartGroupResolverInterface
{
    /**
     * @var CustomerGroupContextInterface
     */
    private $customerGroupContext;

    /**
     * @var GuestCartRepositoryInterface
     */
    private $guestCartRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var GetLastAddedProduct
     */
    private $getLastAddedProduct;

    /**
     * @var ProductGroupResolver
     */
    private $productGroupResolver;

    /**
     * @var GenerateEmptyGroupProductsResult
     */
    private $generateEmptyGroupProductsResult;

    public function __construct(
        CustomerGroupContextInterface $customerGroupContext,
        GuestCartRepositoryInterface $guestCartRepository,
        CartRepositoryInterface $cartRepository,
        GetLastAddedProduct $getLastAddedProduct,
        ProductGroupResolver $productGroupResolver,
        GenerateEmptyGroupProductsResult $generateEmptyGroupProductsResult
    ) {
        $this->customerGroupContext = $customerGroupContext;
        $this->guestCartRepository = $guestCartRepository;
        $this->cartRepository = $cartRepository;
        $this->getLastAddedProduct = $getLastAddedProduct;
        $this->productGroupResolver = $productGroupResolver;
        $this->generateEmptyGroupProductsResult = $generateEmptyGroupProductsResult;
    }

    public function getGroupByMaskedQuoteIdAndPosition(
        string $maskedCartId,
        string $position,
        ?int $customerGroupId = null
    ): GroupProductsResultInterface {
        $quote = $this->guestCartRepository->get($maskedCartId);
        return $this->getGroupByQuoteIdAndPosition((int)$quote->getId(), $position, $customerGroupId);
    }

    public function getGroupByQuoteIdAndPosition(
        int $quoteId,
        string $position,
        ?int $customerGroupId = null
    ): GroupProductsResultInterface {
        $quote = $this->cartRepository->getActive($quoteId);
        $product = $this->getLastAddedProduct->execute($quote);
        if ($product) {
            $this->customerGroupContext->set($customerGroupId);
            return $this->productGroupResolver->getGroupByProductIdAndPosition((int)$product->getId(), $position);
        } else {
            return $this->generateEmptyGroupProductsResult->execute();
        }
    }
}
