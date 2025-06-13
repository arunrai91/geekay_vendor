<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Test\Unit\Model\Rule\Action\Discount;

use Amasty\Rules\Model\Rule\Action\Discount\Thecheapest;
use Amasty\Rules\Model\Rule\ItemCalculationPrice;
use Amasty\Rules\Test\Unit\TestHelper\ObjectCreatorTrait;
use Amasty\Rules\Test\Unit\TestHelper\ReflectionTrait;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class TheCheapestCalculationTest
 *
 * @see Thecheapest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class TheCheapestTest extends \PHPUnit\Framework\TestCase
{
    use ReflectionTrait;
    use ObjectCreatorTrait;

    /**#@+
     * Required data of Item object
     */
    public const ITEM_PRICE = 100.00;
    public const ITEM_BASE_PRICE = 100.00;
    public const ITEM_ORIGINAL_PRICE = 100.00;
    public const ITEM_BASE_ORIGINAL_PRICE = 100.00;
    /**#@-*/

    /**#@+
     * Required data of AbstractRule|Rule object
     */
    public const ITEMS_COUNT = 10;
    public const RULE_DISCOUNT_STEP = 10;
    public const RULE_SIMPLE_ACTION = \Amasty\Rules\Helper\Data::TYPE_CHEAPEST;
    public const RULE_DISCOUNT_QTY = 0;
    public const RULE_DISCOUNT_AMOUNT = 10;
    /**#@-*/

    /**
     * @var AbstractItem|MockObject
     */
    private $item;

    protected function setUp(): void
    {
        $this->initQuote();
    }

    /**
     * @covers Thecheapest::calculateDiscount
     *
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function testCalculateDiscount(): void
    {
        $this->item = $this->initQuoteItem();
        $itemPrice = $this->initItemPrice();

        /** @var Data|MockObject $data */
        $data = $this->createPartialMock(Data::class, []);
        $dataFactory = $this->initDiscountDataFactory($data);

        /** @var Thecheapest $action */
        $action = $this->getObjectManager()->getObject(
            Thecheapest::class,
            [
                'discountDataFactory' => $dataFactory,
                'itemPrice' => $itemPrice,
            ]
        );
        $this->invokeMethod($action, 'calculateDiscount', [$this->item, 1, self::RULE_DISCOUNT_AMOUNT]);

        $this->assertEquals(
            $data->getAmount(),
            self::ITEM_PRICE * self::RULE_DISCOUNT_AMOUNT / 100,
            'Discount calculation: wrong getAmount result.'
        );
        $this->assertEquals(
            $data->getBaseAmount(),
            self::ITEM_BASE_PRICE * self::RULE_DISCOUNT_AMOUNT / 100,
            'Discount calculation: wrong getBaseAmount result.'
        );
        $this->assertEquals(
            $data->getOriginalAmount(),
            self::ITEM_ORIGINAL_PRICE * self::RULE_DISCOUNT_AMOUNT / 100,
            'Discount calculation: wrong getOriginalAmount result.'
        );
        $this->assertEquals(
            $data->getBaseOriginalAmount(),
            self::ITEM_BASE_ORIGINAL_PRICE * self::RULE_DISCOUNT_AMOUNT / 100,
            'Discount calculation: wrong getBaseOriginalAmount result.'
        );
    }

    /**
     * Used validateItems function replaced with stub.
     *
     * @covers \Thecheapest::getAllowedItemsIds
     *
     * @throws \ReflectionException
     */
    public function testGetAllowedItemsIds(): void
    {
        $qty = $this->prepareQuoteItems();

        /** @var Thecheapest|MockObject $action */
        $action = $this->getMockBuilder(Thecheapest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['validateItems'])
            ->getMock();
        $this->setProperty($action, 'validator', $this->initValidator());
        $action->expects($this->any())->method('validateItems')->willReturn($this->items);

        $items = $this->invokeMethod(
            $action,
            'getAllowedItemsIds',
            [
                $this->address,
                $this->initRule()
            ]
        );

        $this->assertCount(
            (int)($qty / static::RULE_DISCOUNT_STEP ?: 1),
            $items,
            'Expected allowed items count mismatch actual'
        );

        /**
         * Use `amrules_id` value of first created test item because of it's smaller than others.
         */
        $firstItem = reset($this->items);
        $this->assertEquals(
            $firstItem->getAmrulesId(),
            reset($items),
            'Field `amrules_id` of first allowed item isn\'t like expected.'
        );
    }

    /**
     * @return ItemCalculationPrice|MockObject
     */
    private function initItemPrice()
    {
        /** @var ItemCalculationPrice|MockObject $itemPrice */
        $itemPrice = $this->createMock(ItemCalculationPrice::class);
        $itemPrice->expects($this->any())
            ->method('getItemPrice')->with($this->item)->willReturn(self::ITEM_PRICE);
        $itemPrice->expects($this->any())
            ->method('getItemBasePrice')->with($this->item)->willReturn(self::ITEM_BASE_PRICE);
        $itemPrice->expects($this->any())
            ->method('getItemOriginalPrice')->with($this->item)->willReturn(self::ITEM_ORIGINAL_PRICE);
        $itemPrice->expects($this->any())
            ->method('getItemBaseOriginalPrice')
            ->with($this->item)
            ->willReturn(self::ITEM_BASE_ORIGINAL_PRICE);

        $amount = self::ITEM_ORIGINAL_PRICE * self::RULE_DISCOUNT_AMOUNT / 100;
        $baseAmount = self::ITEM_BASE_ORIGINAL_PRICE * self::RULE_DISCOUNT_AMOUNT / 100;
        $itemPrice->expects($this->any())
            ->method('resolveFinalPriceRevert')
            ->with($amount, $this->item)
            ->willReturn($amount);
        $itemPrice->expects($this->any())
            ->method('resolveBaseFinalPriceRevert')
            ->with($baseAmount, $this->item)
            ->willReturn($baseAmount);

        return $itemPrice;
    }
}
