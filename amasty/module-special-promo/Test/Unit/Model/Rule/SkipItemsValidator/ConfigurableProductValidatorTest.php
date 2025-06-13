<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Test\Unit\Model\Rule\SkipItemsValidator;

use Amasty\Rules\Model\ConfigModel;
use Amasty\Rules\Model\Rule as AmastyRule;
use Amasty\Rules\Model\Rule\SkipItemsValidator\ConfigurableProductValidator;
use Amasty\Rules\Model\Rule\SkipItemsValidator\SpecialPriceValidator;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers ConfigurableProductValidator
 */
class ConfigurableProductValidatorTest extends TestCase
{
    /**
     * @var Product|MockObject
     */
    private $productMock;

    /**
     * @var ConfigModel|MockObject
     */
    private $configModelMock;

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
        $this->productMock = $this->createMock(Product::class);
        $this->configModelMock = $this->createMock(ConfigModel::class);
        $this->itemMock = $this->createConfiguredMock(
            AbstractItem::class,
            ['getProduct' => $this->productMock]
        );
        $this->ruleMock = $this->createMock(Rule::class);
        $this->amruleMock = $this->createMock(AmastyRule::class);

        $this->subject = new ConfigurableProductValidator(
            $this->configModelMock
        );
    }

    /**
     * @dataProvider validateDataProvider
     */
    public function testValidate(string $productType, int $specialPrice, bool $result): void
    {
        $this->productMock
            ->expects($this->once())
            ->method('getTypeId')
            ->willReturn($productType);
        $this->itemMock->method('getChildren')->willReturn([$this->itemMock]);
        $this->productMock->method('getSpecialPrice')->willReturn($specialPrice);

        $this->assertEquals($result, $this->subject->validate($this->itemMock, $this->ruleMock));
    }

    /**
     * @dataProvider isNeedToValidateDataProvider
     */
    public function testIsNeedToValidate(
        bool $generalSkipSettings,
        string $skipSpecialPriceConfigurable,
        string $skipRule,
        bool $result
    ): void {
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
        $this->configModelMock
            ->expects($this->atMost(1))
            ->method('getSkipSpecialPriceConfigurable')
            ->willReturn($skipSpecialPriceConfigurable);

        $this->assertEquals($result, $this->subject->isNeedToValidate($this->ruleMock));
    }

    public function validateDataProvider(): array
    {
        return [
            'simple product' => ['simple', 10, false],
            'configurable product with special price' => ['configurable', 10, true],
            'configurable product without special price' => ['configurable', 0, false]
        ];
    }

    public function isNeedToValidateDataProvider(): array
    {
        return [
            'general settings with skip configurable special price' => [true, "1", '', true],
            'general settings without skip configurable special price' => [true, "0", '', false],
            'rule settings with skip configurable special price' => [false, "1", '1,4', true],
            'rule settings without skip configurable special price' => [false, "0", '2,3', false],
        ];
    }
}
