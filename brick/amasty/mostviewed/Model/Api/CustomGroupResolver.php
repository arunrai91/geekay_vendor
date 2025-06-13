<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Api;

use Amasty\Mostviewed\Api\CustomGroupResolverInterface;
use Amasty\Mostviewed\Api\Data\GroupProductsResultInterface;
use Amasty\Mostviewed\Model\Api\Group\CustomPosition\ResolveGroupProductsResult;
use Amasty\Mostviewed\Model\Api\Group\Quote\GetLastAddedProduct;
use Amasty\Mostviewed\Model\Customer\CustomerGroupContext;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\GuestCartRepositoryInterface;

class CustomGroupResolver implements CustomGroupResolverInterface
{
    /**
     * @var CustomerGroupContext
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
     * @var ResolveGroupProductsResult
     */
    private $resolveGroupProductsResult;

    public function __construct(
        CustomerGroupContext $customerGroupContext,
        GuestCartRepositoryInterface $guestCartRepository,
        CartRepositoryInterface $cartRepository,
        GetLastAddedProduct $getLastAddedProduct,
        ResolveGroupProductsResult $resolveGroupProductsResult
    ) {
        $this->customerGroupContext = $customerGroupContext;
        $this->guestCartRepository = $guestCartRepository;
        $this->cartRepository = $cartRepository;
        $this->getLastAddedProduct = $getLastAddedProduct;
        $this->resolveGroupProductsResult = $resolveGroupProductsResult;
    }

    public function getGroup(int $groupId, ?int $customerGroupId = null): GroupProductsResultInterface
    {
        $this->customerGroupContext->set($customerGroupId);
        return $this->resolveGroupProductsResult->execute($groupId);
    }

    public function getGroupForProduct(
        int $groupId,
        int $productId,
        ?int $customerGroupId = null
    ): GroupProductsResultInterface {
        $this->customerGroupContext->set($customerGroupId);
        return $this->resolveGroupProductsResult->execute($groupId, $productId);
    }

    public function getGroupForGuestCart(
        int $groupId,
        string $maskedCartId,
        ?int $customerGroupId = null
    ): GroupProductsResultInterface {
        $quote = $this->guestCartRepository->get($maskedCartId);
        return $this->getGroupForCart($groupId, (int)$quote->getId(), $customerGroupId);
    }

    public function getGroupForCart(
        int $groupId,
        int $quoteId,
        ?int $customerGroupId = null
    ): GroupProductsResultInterface {
        $quote = $this->cartRepository->getActive($quoteId);
        $product = $this->getLastAddedProduct->execute($quote);
        if ($product) {
            return $this->getGroupForProduct($groupId, (int)$product->getId());
        } else {
            return $this->getGroup($groupId);
        }
    }
}
