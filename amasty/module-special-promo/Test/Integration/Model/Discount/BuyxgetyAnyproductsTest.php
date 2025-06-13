<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Test\Integration\Model\Discount;

use Amasty\Rules\Helper\Data;
use Amasty\Rules\Model\ResourceModel\Rule as AmRuleResource;
use Amasty\Rules\Model\Rule as AmRule;
use Amasty\Rules\Model\Rule\Action\Discount\BuyxgetyAnyproducts;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\GroupManagement;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\SalesRule\Model\ResourceModel\Rule as SalesRuleResource;
use Magento\SalesRule\Model\Rule;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @covers BuyxgetyAnyproducts
 */
class BuyxgetyAnyproductsTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var Rule|mixed
     */
    private $salesRule;

    /**
     * @var BuyxgetyAnyproducts|mixed
     */
    private $object;

    /**
     * @var SalesRuleResource|mixed
     */
    private $salesRuleResource;

    /**
     * @var AmRuleResource|mixed
     */
    private $amastyRuleResource;

    /**
     * @var AmRule|mixed
     */
    private $amastyRuleModel;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->salesRuleResource = $this->objectManager->create(SalesRuleResource::class);
        $this->amastyRuleResource = $this->objectManager->create(AmRuleResource::class);
        $this->salesRule = $this->createSalesRule();
        $this->amastyRuleModel = $this->objectManager->create(AmRule::class);
        $this->createAmRule((int)$this->salesRule->getRuleId());
        $this->initObjectRule();
    }

    private function createSalesRule(): Rule
    {
        $salesRule = $this->objectManager->create(Rule::class);
        $salesRule->setData(
            [
                'name' => 'Buy 3 products get 1 With Discount 10 Percent',
                'is_active' => 1,
                'customer_group_ids' => [GroupManagement::NOT_LOGGED_IN_ID],
                'coupon_type' => Rule::COUPON_TYPE_NO_COUPON,
                'simple_action' => Data::TYPE_XY_ANY_PRODUCTS,
                'discount_qty' => 1,
                'discount_amount' => 10,
                'discount_step' => 2,
                'stop_rules_processing' => 1,
                'website_ids' => [
                    $this->objectManager->get(
                        StoreManagerInterface::class
                    )->getWebsite()->getId()
                ]
            ]
        );
        $this->salesRuleResource->save($salesRule);

        return $salesRule;
    }

    private function createAmRule(int $salesRuleId): void
    {
        $this->amastyRuleModel = $this->objectManager->create(AmRule::class);
        $this->amastyRuleModel
            ->setData('salesrule_id', $salesRuleId)
            ->setApplyDiscountTo('asc')
            ->setPriceselector(0)
            ->setNqty(1)
            ->setPromoSkus('')
            ->setSkipRule('');

        $this->amastyRuleResource->save($this->amastyRuleModel);

        $this->salesRule->setData('amrules_rule', $this->amastyRuleModel);
    }

    /**
     * discount should be done for the cheapest product
     *
     * @magentoDataFixture Amasty_Rules::Test/Integration/_files/products.php
     * @dataProvider cheapestDataProvider
     * @param string[] $quoteItemsSkus
     * @param int[] $result
     */
    public function testCalculateWithCheapest(array $quoteItemsSkus, string $testedItemSku, array $result): void
    {
        $this->calculateDiscount($quoteItemsSkus, $testedItemSku, $result);
    }

    /**
     * discount should be done for the most expensive product
     *
     * @magentoDataFixture Amasty_Rules::Test/Integration/_files/products.php
     * @dataProvider expensiveDataProvider
     * @param string[] $quoteItemsSkus
     * @param int[] $result
     */
    public function testCalculateWithExpensive(array $quoteItemsSkus, string $testedItemSku, array $result): void
    {
        $this->amastyRuleModel->setApplyDiscountTo('desc');
        $this->calculateDiscount($quoteItemsSkus, $testedItemSku, $result);
    }

    /**
     * @param string[] $quoteItemsSkus
     * @param int[] $result
     */
    public function calculateDiscount(array $quoteItemsSkus, string $testedItemSku, array $result): void
    {
        $testedItem = $this->addProductsToQuote($quoteItemsSkus, $testedItemSku);
        $discountData = $this->object->calculate($this->salesRule, $testedItem, 1);

        $resultArray = [
            'amount' => $discountData->getAmount(),
            'baseAmount' => $discountData->getBaseAmount(),
            'originalAmount' => $discountData->getOriginalAmount(),
            'baseOriginalAmount' => $discountData->getBaseOriginalAmount()
        ];

        $this->assertEquals($result, $resultArray);
    }

    /**
     * @param string[] $quoteItemsSkus
     * @param string $testedItemSku
     */
    private function addProductsToQuote(array $quoteItemsSkus, string $testedItemSku): Item
    {
        $quote = $this->objectManager->create(Quote::class);
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);

        foreach ($quoteItemsSkus as $itemSku) {
            $product = $productRepository->get($itemSku, false, null, true);
            $quoteItem = $quote->addProduct($product);
            $quoteItem->setOriginalPrice($product->getPrice());
            $quoteItem->setBaseOriginalPrice($product->getPrice());
            if ($itemSku === $testedItemSku) {
                $testedItem = $quoteItem;
            }
        }

        return $testedItem;
    }

    public function cheapestDataProvider()
    {
        return [
            'small items qty' => [
                ['simple', 'simple2'],
                'simple',
                [
                    'amount' => 0,
                    'baseAmount' => 0,
                    'originalAmount' => 0,
                    'baseOriginalAmount' => 0
                ],
            ],
            'normal items qty' => [
                ['simple', 'simple2', 'simple3'],
                'simple',
                [
                    'amount' => 1,
                    'baseAmount' => 1,
                    'originalAmount' => 1,
                    'baseOriginalAmount' => 1
                ],
            ],
            'large items qty' => [
                ['simple', 'simple2', 'simple3', 'simple4'],
                'simple',
                [
                    'amount' => 1,
                    'baseAmount' => 1,
                    'originalAmount' => 1,
                    'baseOriginalAmount' => 1
                ],
            ],
            'tested product is not the cheapest' => [
                ['simple', 'simple2', 'simple3'],
                'simple3',
                [
                    'amount' => 0,
                    'baseAmount' => 0,
                    'originalAmount' => 0,
                    'baseOriginalAmount' => 0
                ],
            ],
        ];
    }

    public function expensiveDataProvider()
    {
        return [
            'small items qty' => [
                ['simple', 'simple2'],
                'simple',
                [
                    'amount' => 0,
                    'baseAmount' => 0,
                    'originalAmount' => 0,
                    'baseOriginalAmount' => 0
                ],
            ],
            'normal items qty' => [
                ['simple', 'simple2', 'simple3'],
                'simple3',
                [
                    'amount' => 3,
                    'baseAmount' => 3,
                    'originalAmount' => 3,
                    'baseOriginalAmount' => 3
                ],
            ],
            'large items qty' => [
                ['simple', 'simple2', 'simple3', 'simple4'],
                'simple4',
                [
                    'amount' => 4,
                    'baseAmount' => 4,
                    'originalAmount' => 4,
                    'baseOriginalAmount' => 4
                ],
            ],
            'tested product is not the most expensive' => [
                ['simple', 'simple2', 'simple3'],
                'simple',
                [
                    'amount' => 0,
                    'baseAmount' => 0,
                    'originalAmount' => 0,
                    'baseOriginalAmount' => 0
                ],
            ],
        ];
    }

    protected function initObjectRule(): void
    {
        $this->object = $this->objectManager->create(
            BuyxgetyAnyproducts::class
        );
    }

    protected function tearDown(): void
    {
        $this->salesRuleResource->delete($this->salesRule);
        $this->amastyRuleResource->delete($this->amastyRuleModel);
    }
}
