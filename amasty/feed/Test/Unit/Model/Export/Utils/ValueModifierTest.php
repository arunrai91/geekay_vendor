<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Test\Unit\Model\Export\Utils;

use Amasty\Feed\Model\Export\Utils\ValueModifier;
use Amasty\Feed\Test\Unit\Traits;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see ValueModifier
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class ValueModifierTest extends TestCase
{
    use Traits\ReflectionTrait;

    public const DATE = ['format' => 'date'];
    public const PRICE = ['format' => 'price'];
    public const INTEGER = ['format' => 'integer'];
    public const NO_FORMAT = [];

    /**
     * @covers ValueModifier::formatValue
     * @dataProvider formatValueDataProvider
     */
    public function testFormatValue(array $field, $value, $expectedResult): void
    {
        /** @var ValueModifier|MockObject $model */
        $model = $this->createPartialMock(ValueModifier::class, []);
        $priceOptionsProp = [
            'price_decimals' => 2,
            'price_decimal_point' => ',',
            'price_thousand_separator' => ' ',
            'price_currency_show' => true,
            'price_currency' => '$'
        ];

        $this->setProperty($model, 'formatDate', 'Y', ValueModifier::class);
        $this->setProperty($model, 'formatPriceOptions', $priceOptionsProp, ValueModifier::class);

        $result = $model->formatValue($field, $value);
        $this->assertEquals($expectedResult, $result);
    }

    public function formatValueDataProvider(): array
    {
        return [
            [self::PRICE, 100, '100,00 $'],
            [self::DATE, '10 September 2000', 2000],
            [self::INTEGER, 1, 1],
            [self::NO_FORMAT, 1, 1],
        ];
    }
}
