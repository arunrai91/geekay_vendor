<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Test\Unit\Model\Rule\Action\Discount;

use Amasty\Rules\Model\Rule\Action\Discount\BuyxgetnPerc;
use Amasty\Rules\Model\RuleResolver;
use Amasty\Rules\Test\Unit\TestHelper\ObjectCreatorTrait;
use Amasty\Rules\Test\Unit\TestHelper\ReflectionTrait;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class BuyxgetnPercTest
 *
 * @see BuyxgetnPerc
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class BuyxgetnPercTest extends \PHPUnit\Framework\TestCase
{
    use ReflectionTrait;
    use ObjectCreatorTrait;

    /**#@+
     * Required data of AbstractRule|Rule object
     */
    public const ITEMS_COUNT = 10;
    public const RULE_DISCOUNT_STEP = 10;
    public const RULE_SIMPLE_ACTION = \Amasty\Rules\Helper\Data::TYPE_AMOUNT;
    public const RULE_DISCOUNT_QTY = 0; //Maximum Qty Discount is Applied To
    public const RULE_DISCOUNT_AMOUNT = 1;
    public const RULE_NQTY = 1;
    /**#@-*/

    protected function setUp(): void
    {
        $this->initQuote();
    }

    /**
     * Used validateItems function replaced with stub.
     *
     * @covers BuyxgetnPerc::_calculate
     *
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function testCalculate(): void
    {
        $totalQty = $this->prepareQuoteItems();

        $firstItem = reset($this->items);
        $allowedQty =
            min($firstItem->getQty(), floor(($totalQty - $firstItem->getQty()) / self::RULE_DISCOUNT_STEP * self::RULE_NQTY));
        $expected = $allowedQty * $firstItem->getBaseCalculationPrice() *self::RULE_DISCOUNT_AMOUNT / 100;

        /** @var BuyxgetnPerc|MockObject $action */
        $action = $this->getMockBuilder(BuyxgetnPerc::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['validateItems', 'getAllItems'])
            ->getMock(); // Using mock for replace validateItems method with stub.

        $action->expects($this->any())->method('getAllItems')->willReturn($this->items);
        $action->expects($this->any())->method('validateItems')->willReturn($this->items);

        $this->setProperty($action, 'validator', $this->initValidator());
        $this->setProperty($action, 'discountFactory', $this->initDiscountDataFactory());
        $this->setProperty($action, 'itemPrice', $this->initItemPrice());
        $this->setProperty($action, 'rulesDataHelper', $this->initRulesHelper());

        $rule = $this->initRule();

        $ruleResolver = $this->createConfiguredMock(
            RuleResolver::class,
            ['getSpecialPromotions' => $rule->getAmrulesRule()]
        );
        $this->setProperty($action, 'ruleResolver', $ruleResolver);

        //Case 1: use promo sku
        $rule->getAmrulesRule()->setPromoSkus('simple1')->setData('nqty', self::RULE_NQTY);

        $discountAmount = 0;

        foreach ($this->items as $item) {
            /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $itemDiscount */
            $itemDiscount = $this->invokeMethod($action, '_calculate', [$rule, $item, $rule->getDiscountAmount()]);
            $discountAmount += $itemDiscount->getAmount();
        }

        $this->assertEquals(round($expected, 2), round($discountAmount, 2), 'BuyxgetnPerc case 1 failed.');

        //Case 2: use promo category
        $rule->getAmrulesRule()->setPromoSkus('')->setPromoCats('1')->setData('nqty', self::RULE_NQTY);

        $discountAmount = 0;

        foreach ($this->items as $item) {
            /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $itemDiscount */
            $itemDiscount = $this->invokeMethod($action, '_calculate', [$rule, $item, $rule->getDiscountAmount()]);
            $discountAmount += $itemDiscount->getAmount();
        }

        $this->assertEquals(round($expected, 2), round($discountAmount, 2), 'BuyxgetnPerc case 2 failed.');
    }
}
