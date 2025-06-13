<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Test\Unit\Model\Export\Product\Attributes;

use Amasty\Feed\Model\Export\Product\Attributes\FeedAttributesStorage;
use PHPUnit\Framework\TestCase;

class FeedAttributesStorageTest extends TestCase
{
    /**
     * @dataProvider hasParentAttributesDataProvider
     */
    public function testHasParentAttributes(array $parentAttrs, bool $expected): void
    {
        $storage = new FeedAttributesStorage();
        $storage->setParentAttributes($parentAttrs);

        $result = $storage->hasParentAttributes();
        $this->assertEquals($expected, $result);
    }

    public function hasParentAttributesDataProvider(): array
    {
        return [
            [
                [], false
            ],
            [
                [
                    [
                        'test1' => null
                    ]
                ],
                false
            ],
            [
                [
                    [
                        'test1' => 'test2'
                    ]
                ],
                true
            ]
        ];
    }
}
