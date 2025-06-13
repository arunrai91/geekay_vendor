<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Test\Unit\Model\Rule\Action\Discount;

use Amasty\Rules\Model\Rule\Action\Discount\AbstractRule;
use Amasty\Rules\Test\Unit\TestHelper\ObjectCreatorTrait;
use Amasty\Rules\Test\Unit\TestHelper\ReflectionTrait;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class AbstractDiscountActionTest
 *
 * phpcs:ignoreFile
 */
class AbstractRuleTest extends \PHPUnit\Framework\TestCase
{
    use ReflectionTrait;
    use ObjectCreatorTrait;

    /**#@+
     * Required data of AbstractRule|Rule object
     */
    public const ITEMS_COUNT = 10;
    public const RULE_DISCOUNT_STEP = 2;
    public const RULE_SIMPLE_ACTION = '';
    public const RULE_DISCOUNT_QTY = 20;
    public const RULE_DISCOUNT_AMOUNT = 10;
    /**#@-*/

    protected function setUp(): void
    {
        $this->initQuote();
    }

    /**
     * Test for getSortedItems function.
     * Used validateItems function replaced with stub.
     *
     * @covers \Amasty\Rules\Model\Rule\Action\Discount\AbstractRule::getSortedItems
     * @throws \ReflectionException
     */
    public function testGetSortedItems(): void
    {
        $qty = $this->prepareQuoteItems();

        /** @var AbstractRule|MockObject $action */
        $action = $this->getMockBuilder(AbstractRule::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['validateItems'])
            ->getMockForAbstractClass();
        $this->setProperty($action, 'validator', $this->initValidator());
        $action->expects($this->any())->method('validateItems')->will($this->returnValue($this->items));

        $items = $this->invokeMethod(
            $action,
            'getSortedItems',
            [
                $this->address,
                $this->initRule(),
                AbstractRule::DESC_SORT
            ]
        );

        $this->assertEquals(count($items), $qty, 'Items split failed.');
        $this->assertTrue($this->checkItemsIsSorted($items, AbstractRule::DESC_SORT), 'Items is not sorted.');
    }

    /**
     * Test for skipEachN function.
     * Used validateItems function replaced with stub.
     *
     * @covers \Amasty\Rules\Model\Rule\Action\Discount\AbstractRule::skipEachN
     * @throws \ReflectionException
     */
    public function testSkipEachN(): void
    {
        $qty = $this->prepareQuoteItems();

        /** @var AbstractRule|MockObject $action */
        $action = $this->getMockBuilder(AbstractRule::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['validateItems'])
            ->getMockForAbstractClass();

        $this->setProperty($action, 'validator', $this->initValidator());
        $action->expects($this->any())->method('validateItems')->will($this->returnValue($this->items));

        $items = $this->invokeMethod(
            $action,
            'getSortedItems',
            [
                $this->address,
                $this->initRule(),
                AbstractRule::DESC_SORT
            ]
        );

        /** @var \Magento\SalesRule\Model\Rule $rule */
        $rule = $this->initRule(false)
            ->setDiscountStep(self::RULE_DISCOUNT_STEP)
            ->setDiscountQty(self::RULE_DISCOUNT_QTY)
            ->setSimpleAction(self::RULE_SIMPLE_ACTION);

        /** Case 1: Non-EACH-action. */
        $resItems = $action->skipEachN($items, $rule);
        $this->assertEquals(count($resItems), min($qty, self::RULE_DISCOUNT_QTY), 'Skip EachN Case 1 failed.');

        /** Case 2: @see \Amasty\Rules\Helper\Data::TYPE_EACH_M_AFT_N_DISC action,  */
        $rule->setSimpleAction(\Amasty\Rules\Helper\Data::TYPE_EACH_M_AFT_N_DISC)->setDiscountQty(0);
        $resItems = $action->skipEachN($items, $rule);
        $this->assertEquals(count($resItems), round($qty / 2), 'Skip EachN Case 2 failed.');

        /** Case 3: @see \Amasty\Rules\Helper\Data::TYPE_GROUP_N_DISC action,  */
        $rule->setSimpleAction(\Amasty\Rules\Helper\Data::TYPE_GROUP_N_DISC)->setDiscountQty(0);
        $resItems = $action->skipEachN($items, $rule);
        $this->assertEquals($qty, count($resItems), 'Skip EachN Case 3 failed.');
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item[] $items
     * @param string $order
     *
     * @return bool
     */
    protected function checkItemsIsSorted(array $items, string $order = AbstractRule::ASC_SORT): bool
    {
        if ($order === AbstractRule::DESC_SORT) {
            $items = array_reverse($items);
        }

        $prevValue = current($items);

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($items as $item) {
            if ($item->getBaseCalculationPrice() >= $prevValue->getBaseCalculationPrice()) {
                $prevValue = $item;
                continue;
            }

            return false;
        }

        return true;
    }
}
