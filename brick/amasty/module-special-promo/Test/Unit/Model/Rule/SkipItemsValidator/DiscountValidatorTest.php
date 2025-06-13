<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Test\Unit\Model\Rule\SkipItemsValidator;

use Amasty\Rules\Model\Rule as AmastyRule;
use Amasty\Rules\Model\Rule\SkipItemsValidator\DiscountValidator;
use Amasty\Rules\Model\Rule\SkipItemsValidator\SpecialPriceValidator;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers DiscountValidator
 */
class DiscountValidatorTest extends TestCase
{
    private const RULE_ID = '1';

    /**
     * @var AbstractItem|MockObject
     */
    private $itemMock;

    /**
     * @var Rule|MockObject
     */
    private $ruleMock;

    /**
     * @var AmastyRule|MockObject
     */
    private $amruleMock;

    /**
     * @var SpecialPriceValidator
     */
    private $subject;

    protected function setUp(): void
    {
        $this->itemMock = $this->createMock(AbstractItem::class);
        $this->ruleMock = $this->createConfiguredMock(Rule::class, ['getId' => self::RULE_ID]);
        $this->amruleMock = $this->createMock(AmastyRule::class);

        $this->subject = new DiscountValidator();
    }

    /**
     * @dataProvider validateDataProvider
     */
    public function testValidate(int $discountAmount, string $appliedRuleIds, bool $result): void
    {
        $this->itemMock
            ->expects($this->atLeast(1))
            ->method('getData')
            ->willReturnMap(
                [
                    ['discount_amount', null, $discountAmount],
                    ['applied_rule_ids', null, $appliedRuleIds],
                ]
            );

        $this->assertEquals($result, $this->subject->validate($this->itemMock, $this->ruleMock));
    }

    /**
     * @dataProvider isNeedToValidateDataProvider
     */
    public function testIsNeedToValidate(bool $generalSkipSettings, string $skipRule, bool $result): void
    {
        $this->ruleMock
            ->expects($this->once())
            ->method('getData')
            ->with('amrules_rule', null)
            ->willReturn($this->amruleMock);
        $this->amruleMock
            ->expects($this->once())
            ->method('isEnableGeneralSkipSettings')
            ->willReturn($generalSkipSettings);
        $this->amruleMock->expects($this->once())->method('getSkipRule')->willReturn($skipRule);

        $this->assertEquals($result, $this->subject->isNeedToValidate($this->ruleMock));
    }

    public function validateDataProvider(): array
    {
        return [
            'product with discount and same rule' => [10, '1', false],
            'product with discount and other rule' => [10, '2', true],
            'product without discount' => [0, '', false]
        ];
    }

    public function isNeedToValidateDataProvider(): array
    {
        return [
            'enable general settings' => [true, '', false],
            'disable general settings without skip discount' => [false, '1', false],
            'disable general settings with skip discount' => [false, '1,3', true],
        ];
    }
}
