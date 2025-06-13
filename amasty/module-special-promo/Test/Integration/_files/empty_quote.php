<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

Bootstrap::getInstance()->loadArea('frontend');

$objectManager = Bootstrap::getObjectManager();

$addressData = [
    'region' => 'CA',
    'region_id' => '12',
    'postcode' => '11111',
    'company' => 'Test Company',
    'lastname' => 'lastname',
    'firstname' => 'firstname',
    'street' => 'street',
    'city' => 'Los Angeles',
    'email' => 'admin@example.com',
    'telephone' => '11111111',
    'country_id' => 'US'
];
$billingAddress = $objectManager->create(Address::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');

$storeManager = $objectManager->get(StoreManagerInterface::class);
$store = $storeManager->getStore();

/** @var Quote $quote */
$quote = $objectManager->create(Quote::class);
$quote->setCustomerIsGuest(true)
    ->setStoreId($store->getId())
    ->setReservedOrderId('test01')
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->setCouponCode('test');
$quote->getPayment()->setMethod('checkmo');
$quote->setIsMultiShipping(1);
$quote->getShippingAddress()
    ->setShippingMethod('freeshipping_freeshipping')
    ->setCollectShippingRates(true);

$quoteRepository = $objectManager->get(CartRepositoryInterface::class);
$quoteRepository->save($quote);
