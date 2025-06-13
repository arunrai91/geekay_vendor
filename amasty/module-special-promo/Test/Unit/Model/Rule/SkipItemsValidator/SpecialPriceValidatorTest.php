<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Test\Unit\Model\Rule\SkipItemsValidator;

use Amasty\Rules\Model\ConfigModel;
use Amasty\Rules\Model\ResourceModel\Product\CatalogPriceRule;
use Amasty\Rules\Model\Rule as AmastyRule;
use Amasty\Rules\Model\Rule\QuoteStorage;
use Amasty\Rules\Model\Rule\SkipItemsValidator\SpecialPriceValidator;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\SpecialPrice;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers SpecialPriceValidator
 */
class SpecialPriceValidatorTest extends TestCase
{
    private const WEBSITE_CODE = 1;
    private const CUSTOMER_GROUP = 0;
    private const PRODUCT_ID = 12;

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
     * @var CatalogPriceRule|MockObject
     */
    private $catalogPriceRuleMock;

    /**
     * @var QuoteStorage|MockObject
     */
    private $quoteStorageMock;

    /**
     * @var SpecialPrice|MockObject
     */
    private $specialPriceMock;

    /**
     * @var SpecialPriceValidator
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
        $this->catalogPriceRuleMock = $this->createMock(CatalogPriceRule::class);
        $this->specialPriceMock = $this->createMock(SpecialPrice::class);
        $priceInfoMock = $this->createConfiguredMock(
            Base::class,
            [
                'getPrice' => $this->specialPriceMock,
            ]
        );
        $this->productMock = $this->createConfiguredMock(
            Product::class,
            ['getPriceInfo' => $priceInfoMock]
        );
        $this->configModelMock = $this->createMock(ConfigModel::class);
        $quote = $this->createMock(CartInterface::class);
        $this->itemMock = $this->createConfiguredMock(
            AbstractItem::class,
            [
                'getProduct' => $this->productMock,
                'getQuote' => $quote,
            ]
        );
        $this->ruleMock = $this->createMock(Rule::class);
        $this->amruleMock = $this->createMock(AmastyRule::class);
        $this->quoteStorageMock = $this->createMock(QuoteStorage::class);

        $this->subject = new SpecialPriceValidator(
            $this->catalogPriceRuleMock,
            $storeManagerMock,
            $customerSessionMock,
            $this->configModelMock,
            $this->quoteStorageMock
        );
    }

    /**
     * @param bool|string $catalogRuleProduct
     * @dataProvider validateDataProvider
     */
    public function testValidate(int $specialPrice, $catalogRuleProduct, bool $result): void
    {
        $this->specialPriceMock->method('getValue')->willReturn($specialPrice);
        $this->productMock->method('getId')->willReturn(self::PRODUCT_ID);
        $this->quoteStorageMock->method('getProductsWithDiscount')->willReturn([$catalogRuleProduct]);

        $this->assertEquals($result, $this->subject->validate($this->itemMock, $this->ruleMock));
    }

    /**
     * @dataProvider isNeedToValidateDataProvider
     */
    public function testIsNeedToValidate(
        bool $generalSkipSettings,
        string $skipSpecialPrice,
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
            ->method('getSkipSpecialPrice')
            ->willReturn($skipSpecialPrice);

        $this->assertEquals($result, $this->subject->isNeedToValidate($this->ruleMock));
    }

    public function validateDataProvider(): array
    {
        return [
            'product with special price' => [10, 12, true],
            'product without special price and with catalog rule' => [0, 12, true],
            'product without special price and without catalog rule' => [0, false, false]
        ];
    }

    public function isNeedToValidateDataProvider(): array
    {
        return [
            'general settings with skip special price' => [true, "1", '', true],
            'general settings without skip special price' => [true, "0", '', false],
            'rule settings with skip special price' => [false, "1", '1,2', true],
            'rule settings without skip special price' => [false, "0", '2,3', false],
        ];
    }
}
