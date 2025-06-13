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
use Amasty\Rules\Model\Rule\SkipItemsValidator\TierPriceValidator;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers TierPriceValidator
 */
class TierPriceValidatorTest extends TestCase
{
    private const WEBSITE_CODE = '1';
    private const CUSTOMER_GROUP = '0';
    private const PRODUCT_QTY = 1;

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
     * @var TierPriceValidator
     */
    private $subject;

    protected function setUp(): void
    {
        $customerSessionMock = $this->createConfiguredMock(
            Session::class,
            ['getCustomerGroupId'=> self::CUSTOMER_GROUP]
        );
        $websiteMock = $this->createConfiguredMock(Website::class, ['getId' => self::WEBSITE_CODE]);
        $storeManagerMock = $this->createConfiguredMock(
            StoreManagerInterface::class,
            ['getWebsite' => $websiteMock]
        );
        $this->productMock = $this->createMock(Product::class);
        $this->configModelMock = $this->createMock(ConfigModel::class);
        $this->itemMock = $this->createConfiguredMock(
            AbstractItem::class,
            ['getProduct' => $this->productMock, 'getQty' => self::PRODUCT_QTY]
        );
        $this->ruleMock = $this->createMock(Rule::class);
        $this->amruleMock = $this->createMock(AmastyRule::class);

        $this->subject = new TierPriceValidator(
            $storeManagerMock,
            $customerSessionMock,
            $this->configModelMock
        );
    }

    /**
     * @dataProvider validateDataProvider
     */
    public function testValidate(array $tierPrice, bool $result): void
    {
        $this->productMock->method('getTierPrice')->willReturn($tierPrice);

        $this->assertEquals($result, $this->subject->validate($this->itemMock, $this->ruleMock));
    }

    /**
     * @dataProvider isNeedToValidateDataProvider
     */
    public function testIsNeedToValidate(
        bool $generalSkipSettings,
        string $skipTierPrice,
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
            ->method('getSkipTierPrice')
            ->willReturn($skipTierPrice);

        $this->assertEquals($result, $this->subject->isNeedToValidate($this->ruleMock));
    }

    public function validateDataProvider(): array
    {
        return [
            'product without tier price' => [[], false],
            'tier price for other customer group' => [
                [['cust_group' => '1', 'website_id' => '1', 'price_qty' => '1']],
                false
            ],
            'tier price for other website' => [[['cust_group' => '0', 'website_id' => '2', 'price_qty' => '1']], false],
            'tier price for other qty' => [[['cust_group' => '0', 'website_id' => '2', 'price_qty' => '2']], false],
            'product with proper tier price' => [[['cust_group' => '0', 'website_id' => '1', 'price_qty' => '1']], true]
        ];
    }

    public function isNeedToValidateDataProvider(): array
    {
        return [
            'general settings with skip tier price' => [true, "1", '', true],
            'general settings without skip tier price' => [true, "0", '', false],
            'rule settings with skip tier price' => [false, "1", '1,2', true],
            'rule settings without skip tier price' => [false, "0", '3,4', false],
        ];
    }
}
