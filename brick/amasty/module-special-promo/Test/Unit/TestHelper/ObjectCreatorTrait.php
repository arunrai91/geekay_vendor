<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Test\Unit\TestHelper;

use Amasty\Rules\Model\Rule;
use Amasty\Rules\Model\Rule\ItemCalculationPrice;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule as SalesRule;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

/**
 * Suite of useful methods, that create ready-to-use objects.
 *
 * phpcs:ignoreFile
 */
trait ObjectCreatorTrait
{
    /**
     * @var \Magento\Quote\Model\Quote|MockObject
     */
    private $quote;

    /**
     * @var \Magento\Quote\Model\Quote\Address|MockObject
     */
    private $address;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var array
     */
    private $items = [];

    /**
     * @return \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private function getObjectManager()
    {
        if (!$this->objectManager) {
            $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        }

        return $this->objectManager;
    }

    private function initQuote()
    {
        $this->quote = $this->createMock(\Magento\Quote\Model\Quote::class);
        $this->address = $this->createMock(\Magento\Quote\Model\Quote\Address::class);

        $this->quote->expects($this->any())->method('getBillingAddress')->will($this->returnValue($this->address));
        $this->quote->expects($this->any())->method('getShippingAddress')->willReturn($this->address);
        $this->quote->expects($this->any())->method('getAllItems')->willReturnCallback(
            function () {
                return $this->items;
            }
        );

        $this->address->expects($this->any())->method('getAllItems')->willReturnCallback(
            function () {
                return $this->items;
            }
        );

        $this->address->method('getQuote')->willReturn($this->quote);
    }

    /**
     * @param bool $mock
     *
     * @return Item|AbstractItem|MockObject
     */
    private function initQuoteItem($mock = true)
    {
        /** @var AbstractItem|MockObject $item */
        if ($mock) {
            $item = $this->createMock(AbstractItem::class);

            $item->expects($this->any())->method('getQuote')->will($this->returnValue($this->quote));
            $item->expects($this->any())->method('getAddress')->will($this->returnValue($this->address));
        } else {
            $item = $this->getObjectManager()->getObject(Item::class);
            $item->setQuote($this->quote);
        }

        return $item;
    }

    /**
     * @param bool $randomQty
     *
     * @return float|int
     *
     * @throws \Exception
     */
    private function prepareQuoteItems($randomQty = true)
    {
        $itemId = 1;
        $totalQty = 0;
        $totalAmount = 0;

        while ($itemId <= static::ITEMS_COUNT) {
            $item = $this->initQuoteItem(false);
            $qty = $randomQty ? rand(1, 10) : 1;
            $item->setId($itemId)
                ->setBaseCalculationPrice($itemId * 10)
                ->setAmrulesId($itemId)->setData(Item::KEY_QTY, $qty)
                ->setProductId($itemId)
                ->setSku("simple{$itemId}")
                ->setCategoryIds([$itemId])
                ->setData('product', $this->initProduct($itemId, $item->getSku()), $itemId . '');
            $this->items[$itemId] = $item;
            $totalAmount += $itemId * 10;
            $totalQty += $qty;
            $itemId++;
        }

        return $randomQty ? $totalQty : $totalAmount;
    }

    /**
     * @return \Magento\SalesRule\Model\Validator|MockObject
     */
    private function initValidator()
    {
        /** @var \Magento\SalesRule\Model\Validator|MockObject $validator */
        $validator = $this->createPartialMock(
            \Magento\SalesRule\Model\Validator::class,
            ['getItemBasePrice', 'getItemOriginalPrice', 'getItemPrice', 'getItemBaseOriginalPrice']
        );
        $validator->expects($this->any())
            ->method('getItemBasePrice')
            ->willReturnCallback(
                function ($item) {
                    return $item->getBaseCalculationPrice();
                }
            );
        $validator->expects($this->any())
            ->method('getItemOriginalPrice')
            ->willReturnCallback(
                function ($item) {
                    return $item->getBaseCalculationPrice();
                }
            );
        $validator->expects($this->any())
            ->method('getItemPrice')
            ->willReturnCallback(
                function ($item) {
                    return $item->getBaseCalculationPrice();
                }
            );
        $validator->expects($this->any())
            ->method('getItemBaseOriginalPrice')
            ->willReturnCallback(
                function ($item) {
                    return $item->getBaseCalculationPrice();
                }
            );

        return $validator;
    }

    /**
     * @param bool $mock
     *
     * @return Rule|MockObject|SalesRule
     */
    private function initRule($mock = true)
    {
        if ($mock) {
            /** @var Rule|MockObject $rule */
            $rule = $this->getMockBuilder(Rule::class)
                ->disableOriginalConstructor()
                ->addMethods(['getDiscountQty', 'getDiscountStep', 'getDiscountAmount', 'getSimpleAction', 'getAmrulesRule'])
                ->getMock();

            $rule->expects($this->any())->method('getDiscountStep')->will($this->returnValue(static::RULE_DISCOUNT_STEP));
            $rule->expects($this->any())->method('getSimpleAction')->will($this->returnValue(static::RULE_SIMPLE_ACTION));
            $rule->expects($this->any())->method('getDiscountQty')->will($this->returnValue(static::RULE_DISCOUNT_QTY));
            $rule->expects($this->any())->method('getDiscountAmount')->will($this->returnValue(static::RULE_DISCOUNT_AMOUNT));
            $rule->expects($this->any())->method('getAmrulesRule')->will($this->returnValue($this->initAmastyRule()));
        } else {
            $rule = $this->getObjectManager()->getObject(SalesRule::class);
            $rule->setAmrulesRule($this->initAmastyRule());
        }

        return $rule;
    }

    /**
     * @return Rule|object
     */
    private function initAmastyRule()
    {
        return $this->getObjectManager()->getObject(Rule::class);
    }


    /**
     * @return \Amasty\Rules\Helper\Product|MockObject
     */
    private function initItemPrice()
    {
        /** @var ItemCalculationPrice|MockObject $itemPrice */
        $itemPrice = $this->createMock(ItemCalculationPrice::class);
        $itemPrice->expects($this->any())
            ->method('getItemPrice')
            ->willReturnCallback(
                function ($item) {
                    return $item->getBaseCalculationPrice();
                }
            );
        $itemPrice->expects($this->any())
            ->method('getItemBasePrice')
            ->willReturnCallback(
                function ($item) {
                    return $item->getBaseCalculationPrice();
                }
            );
        $itemPrice->expects($this->any())
            ->method('getItemOriginalPrice')
            ->willReturnCallback(
                function ($item) {
                    return $item->getBaseCalculationPrice();
                }
            );
        $itemPrice->expects($this->any())
            ->method('getItemBaseOriginalPrice')
            ->willReturnCallback(
                function ($item) {
                    return $item->getBaseCalculationPrice();
                }
            );
        $itemPrice->expects($this->any())
            ->method('resolveFinalPriceRevert')
            ->willReturnCallback(
                function ($discountAmount, $item) {
                    return $discountAmount;
                }
            );
        $itemPrice->expects($this->any())
            ->method('resolveBaseFinalPriceRevert')
            ->willReturnCallback(
                function ($baseDiscountAmount, $item) {
                    return $baseDiscountAmount;
                }
            );

        return $itemPrice;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule\Action\Discount\Data|MockObject $data
     *
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory|MockObject
     *
     * @throws \Exception
     */
    private function initDiscountDataFactory($data = null)
    {
        if (!class_exists(\Magento\SalesRule\Model\Rule\Action\Discount\DataFactory::class)) {
            throw new \Exception("Factory class was not found. Run generation php bin/magento s:di:co");
        }

        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory|MockObject $dataFactory */
        $dataFactory = $this->createPartialMock(
            \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory::class,
            ['create']
        );
        if ($data) {
            $dataFactory->expects($this->any())->method('create')->will($this->returnValue($data));
        } else {
            $dataFactory->expects($this->any())
                ->method('create')
                ->willReturnCallback(
                    function () {
                        return $this->getObjectManager()->getObject(\Magento\SalesRule\Model\Rule\Action\Discount\Data::class, []);
                    }
                );
        }

        return $dataFactory;
    }

    /**
     * @return \Magento\Framework\Pricing\PriceCurrencyInterface|MockObject
     */
    private function initPriceCurrency()
    {
        $priceCurrency = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)
            ->getMock();
        $priceCurrency->expects($this->any())->method('convert')->will($this->returnArgument(0));
        $priceCurrency->expects($this->any())->method('round')->will($this->returnArgument(0));

        return $priceCurrency;
    }

    /**
     * @return object|\Amasty\Rules\Helper\Data
     */
    private function initRulesHelper()
    {
        return $this->getObjectManager()->getObject(\Amasty\Rules\Helper\Data::class);
    }

    /**
     * @param int $productId
     * @param string $sku
     * @param string $categoryIds
     *
     * @return object|\Magento\Catalog\Model\Product
     * @throws \Exception
     */
    private function initProduct($productId, $sku, $categoryIds = '1')
    {
        $product = $this->objectManager->getObject(\Magento\Catalog\Model\Product::class);
        $product->setId($productId)->setSku($sku)->setCategoryIds($categoryIds);

        /** @var MockObject|\Magento\Catalog\Model\Product\Type\AbstractType $typeInstance */
        $typeInstance = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\AbstractType::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSku'])
            ->getMockForAbstractClass();

        $typeInstance->expects($this->any())
            ->method('getSku')
            ->willReturnCallback(
                function ($product) {
                    return $product->getData('sku');
                }
            );
        $product->setTypeInstance($typeInstance);

        return $product;
    }
}
