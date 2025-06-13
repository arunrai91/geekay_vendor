<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Test\Integration\Model\Discount;

use Amasty\Rules\Model\ResourceModel\Rule as AmRuleResource;
use Amasty\Rules\Model\Rule as AmRule;
use Amasty\Rules\Model\Rule\Action\Discount\AbstractSetof;
use Amasty\Rules\Model\Rule\Action\Discount\SetofFixed;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @covers SetofFixed
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class SetofFixedTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var AmRuleResource
     */
    private $amastyRuleResource;

    /**
     * @var AmRule
     */
    private $amastyRuleModel;

    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var Quote
     */
    private $quote;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->criteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $this->amastyRuleResource = $this->objectManager->create(AmRuleResource::class);
        $this->initiateQuote();
        $this->loadRule();
    }

    /**
     * @magentoDataFixture Amasty_Rules::Test/Integration/_files/products_with_categories.php
     * @magentoDataFixture Amasty_Rules::Test/Integration/_files/empty_quote.php
     * @magentoDataFixture Amasty_Rules::Test/Integration/_files/cart_rule_set_of_fixed.php
     *
     * @dataProvider skuDataProvider
     *
     * @param string[] $productSkus
     * @param int $expectedDiscount
     */
    public function testCalculateWithSkus(array $productSkus, int $expectedDiscount): void
    {
        $this->updateAmruleData('simple3category,simple2category');
        $this->calculateDiscount($productSkus, $expectedDiscount);
    }

    /**
     * @magentoDataFixture Amasty_Rules::Test/Integration/_files/products_with_categories.php
     * @magentoDataFixture Amasty_Rules::Test/Integration/_files/empty_quote.php
     * @magentoDataFixture Amasty_Rules::Test/Integration/_files/cart_rule_set_of_fixed.php
     *
     * @dataProvider catsDataProvider
     *
     * @param string[] $productSkus
     * @param int $expectedDiscount
     */
    public function testCalculateWithCats(array $productSkus, int $expectedDiscount): void
    {
        $this->updateAmruleData('', '1,2');
        $this->calculateDiscount($productSkus, $expectedDiscount);
    }

    /**
     * @magentoDataFixture Amasty_Rules::Test/Integration/_files/products_with_categories.php
     * @magentoDataFixture Amasty_Rules::Test/Integration/_files/empty_quote.php
     * @magentoDataFixture Amasty_Rules::Test/Integration/_files/cart_rule_set_of_fixed.php
     *
     * @dataProvider mixedDataProvider
     *
     * @param string[] $productSkus
     * @param string $promoCats
     * @param int $expectedDiscount
     */
    public function testCalculateWithMixed(array $productSkus, string $promoCats, int $expectedDiscount): void
    {
        $this->updateAmruleData('simple1category', $promoCats);
        $this->calculateDiscount($productSkus, $expectedDiscount);
    }

    private function initiateQuote()
    {
        $searchCriteria = $this->criteriaBuilder->addFilter('reserved_order_id', 'test01')->create();
        $quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $carts = $quoteRepository->getList($searchCriteria)
            ->getItems();
        if (!$carts) {
            throw new \RuntimeException('Cart from fixture not found');
        }

        $this->quote = array_shift($carts);
        $this->quote->getShippingAddress()
            ->setShippingMethod('freeshipping_freeshipping')
            ->setCollectShippingRates(true);

        $this->quote->setCouponCode('test');
    }

    private function loadRule(): void
    {
        $searchCriteria = $this->criteriaBuilder->addFilter('name', 'Fixed price for product set')
            ->create();
        $ruleRepository = $this->objectManager->get(RuleRepositoryInterface::class);
        $items = $ruleRepository->getList($searchCriteria)
            ->getItems();

        $dataModel = array_pop($items);
        $ruleModel = $ruleRepository->getById($dataModel->getRuleId());
        $this->amastyRuleModel = $ruleModel->getExtensionAttributes()->getAmrules();

        $this->amastyRuleResource->save($this->amastyRuleModel);
    }

    private function updateAmruleData(string $promoSkus = '', string $promoCats = ''): void
    {
        $this->amastyRuleModel->setPromoSkus($promoSkus);
        $this->amastyRuleModel->setPromoCats($promoCats);
        $this->amastyRuleResource->save($this->amastyRuleModel);
    }

    private function calculateDiscount(array $productSkus, int $expectedDiscount): void
    {
        AbstractSetof::$allItems = null;
        $this->addProductsToQuote($productSkus);
        $this->quote->collectTotals();

        $this->assertEquals($expectedDiscount, $this->quote->getShippingAddress()->getDiscountAmount());
    }

    private function addProductsToQuote(array $quoteItemsSkus): void
    {
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);

        foreach ($quoteItemsSkus as $itemSku) {
            $product = $productRepository->get($itemSku, false, null, true);
            $this->quote->addProduct($product);
        }
    }

    public function skuDataProvider()
    {
        return [
            'no product set' => [
                ['simple1category', 'simple2category'],
                0
            ],
            'one product set' => [
                ['simple3category', 'simple2category'],
                -40
            ],
            'two product sets' => [
                ['simple3category', 'simple2category', 'simple3category', 'simple2category'],
                -80
            ],
            'product set with additional products' => [
                ['simple3category', 'simple1category', 'simple2category'],
                -40
            ],
            'product set with not full set' => [
                ['simple3category', 'simple3category', 'simple2category'],
                -40
            ],
        ];
    }

    public function catsDataProvider()
    {
        return [
            'no product set' => [
                ['simple3category', 'simple2category'],
                0
            ],
            'one product set' => [
                ['simple1category', 'simple2category'],
                -20
            ],
            'two product sets with equal products' => [
                ['simple1category', 'simple2category', 'simple1category', 'simple2category'],
                -40
            ],
            'two product sets with different products' => [
                ['simple1category', '2simple1category', 'simple2category', 'simple2category'],
                -80
            ],
            'product set with additional products' => [
                ['simple3category', 'simple1category', 'simple2category'],
                -20
            ],
            'product set with not full set' => [
                ['simple1category', 'simple1category', 'simple2category'],
                -20
            ],
            'product set with products with several categories' => [
                ['simple1category', 'simple1-2category'],
                -40
            ],
            'product set with products with several categories 2' => [
                ['simple1-2category', 'simple1-2category'],
                -70
            ],
            'product set with products with several categories 3' => [
                ['simple1-2category', 'simple1-2category', '2simple1category'],
                -70
            ],
            'product set with products with several categories 4' => [
                ['simple1-2category', 'simple1-2category', 'simple1category'],
                -40
            ],
        ];
    }

    public function mixedDataProvider()
    {
        return [
            'no sku in quote' => [
                ['simple3category', 'simple2category'],
                '2,3',
                0
            ],
            'no category in quote' => [
                ['simple1category', 'simple2category'],
                '2,3',
                0
            ],
            'no category in quote2' => [
                ['simple1category'],
                '2,3',
                0
            ],
            'product set' => [
                ['simple1category', 'simple2category', 'simple3category'],
                '2,3',
                -50
            ],
            'two product sets with different products' => [
                [
                    'simple1category',
                    'simple2category',
                    'simple3category',
                    'simple1category',
                    'simple1-2category',
                    'simple3category'
                ],
                '2,3',
                -120
            ],
            'two product sets with equal products' => [
                [
                    'simple1category',
                    'simple2category',
                    'simple3category',
                    'simple1category',
                    'simple2category',
                    'simple3category'
                ],
                '2,3',
                -100
            ],
            'no product set with product suitable for sku and cats' => [
                ['simple1category', 'simple1category', 'simple2category'],
                '1,2',
                0
            ],
            'product set with products suitable for sku and cats' => [
                ['simple1category', 'simple1-2category', 'simple2category'],
                '1,2',
                -60
            ]
        ];
    }
}
