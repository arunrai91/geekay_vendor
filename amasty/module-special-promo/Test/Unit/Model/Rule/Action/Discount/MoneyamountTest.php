<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Test\Unit\Model\Rule\Action\Discount;

use Amasty\Rules\Model\Rule\Action\Discount\Moneyamount;
use Amasty\Rules\Model\Rule\DiscountStorage;
use Amasty\Rules\Test\Unit\TestHelper\ObjectCreatorTrait;
use Amasty\Rules\Test\Unit\TestHelper\ReflectionTrait;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class MoneyamountTest
 *
 * @see Moneyamount
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class MoneyamountTest extends \PHPUnit\Framework\TestCase
{
    use ReflectionTrait;
    use ObjectCreatorTrait;

    /**#@+
     * Required data of AbstractRule|Rule object
     */
    public const ITEMS_COUNT = 10;
    public const RULE_DISCOUNT_STEP = 20;
    public const RULE_SIMPLE_ACTION = \Amasty\Rules\Helper\Data::TYPE_AMOUNT;
    public const RULE_DISCOUNT_QTY = 0; //Maximum Qty Discount is Applied To
    public const RULE_DISCOUNT_AMOUNT = 1;
    /**#@-*/

    protected function setUp(): void
    {
        $this->initQuote();
    }

    /**
     * Used validateItems function replaced with stub.
     *
     * @covers Moneyamount::_calculate
     * @TODO: broken test, fix it
     *
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function testCalculate(): void
    {
        $total = $this->prepareQuoteItems(false);

        /** @var Moneyamount|MockObject $action */
        $action = $this->getMockBuilder(Moneyamount::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['validateItems', 'getAllItems'])
            ->getMock(); // Using mock for replace validateItems method with stub.

        $this->setProperty($action, 'validator', $this->initValidator());
        $action->expects($this->any())->method('getAllItems')->willReturn($this->items);
        $action->expects($this->any())->method('validateItems')->will($this->returnValue($this->items));
        $data = $this->getObjectManager()->getObject(\Magento\SalesRule\Model\Rule\Action\Discount\Data::class);
        $this->setProperty($action, 'discountFactory', $this->initDiscountDataFactory($data));
        $this->setProperty($action, 'itemPrice', $this->initItemPrice());
        $this->setProperty($action, 'discountStorage', $this->initDiscountStorage());

        $discountAmount = 0;

        foreach ($this->items as $item) {
            /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $itemDiscount */
            $itemDiscount = $this->invokeMethod($action, '_calculate', [$this->initRule(), $item]);
            $discountAmount += $itemDiscount->getAmount();
        }

        $this->assertEquals(floor($total / self::RULE_DISCOUNT_STEP) * self::RULE_DISCOUNT_AMOUNT, $discountAmount);
    }

    /**
     * @return DiscountStorage|MockObject
     */
    private function initDiscountStorage()
    {
        return $this->createMock(DiscountStorage::class);
    }
}
