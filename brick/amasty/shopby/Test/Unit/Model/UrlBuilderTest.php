<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Test\Unit\Model;

use Amasty\ShopbyBase\Api\UrlBuilder\AdapterInterface;
use Amasty\ShopbyBase\Model\UrlBuilder;
use Amasty\ShopbyBase\Test\Unit\Traits;

/**
 * Class UrlBuilderTest
 *
 * @see UrlBuilder
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class UrlBuilderTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ReflectionTrait;
    use Traits\ObjectManagerTrait;

    /**
     * @covers UrlBuilder::getUrl
     */
    public function testGetUrl()
    {
        $builderModel = $this->getObjectManager()->getObject(UrlBuilder::class);
        $this->assertNull($builderModel->getUrl());
    }

    /**
     * @covers       UrlBuilder::initAdapters
     *
     * @dataProvider getDataToInitAdapters
     *
     * @param array $urlAdapters
     * @param array $expectedResult
     *
     * @throws \ReflectionException
     */
    public function testInitAdapters($urlAdapters, $expectedResult)
    {
        $builderModel = $this->getObjectManager()->getObject(UrlBuilder::class);
        $this->invokeMethod($builderModel, 'initAdapters', [$urlAdapters]);
        $urlAdaptersResult = $this->getProperty($builderModel, 'urlAdapters');

        $this->assertEmpty(array_diff($expectedResult, array_keys($urlAdaptersResult)));
        $this->assertEmpty(array_diff(array_keys($urlAdaptersResult), $expectedResult));

        $allIsAdapters = true;

        foreach ($urlAdaptersResult as $urlAdapter) {
            if (!($urlAdapter instanceof AdapterInterface)) {
                $allIsAdapters = false;
            }
        }

        $this->assertTrue($allIsAdapters);
    }

    /**
     * @return array
     */
    public function getDataToInitAdapters()
    {
        return [
            [
                [
                    'base' => [
                        'adapter' => $this->getObjectManager()->getObject(
                            \Amasty\ShopbyBase\Model\UrlBuilder\Adapter::class
                        ),
                        'sort_order' => "100"
                    ],
                    'category' => [
                        'adapter' => $this->getObjectManager()->getObject(
                            \Amasty\Shopby\Model\UrlBuilder\CategoryAdapter::class
                        ),
                        'sort_order' => "50"
                    ],
                    'brand' => [
                        'adapter' => $this->getObjectManager()->getObject(
                            \Amasty\ShopbyBrand\Model\UrlBuilder\Adapter::class
                        ),
                        'sort_order' => "10"
                    ]
                ],
                [
                    10,
                    50,
                    100
                ]
            ],
            [
                [
                    'base' => [
                        'adapter' => null,
                        'sort_order' => "100"
                    ],
                    'category' => [
                        'adapter' => $this->getObjectManager()->getObject(
                            \Amasty\Shopby\Model\UrlBuilder\CategoryAdapter::class
                        ),
                        'sort_order' => "50"
                    ],
                    'brand' => [
                        'adapter' => $this->getObjectManager()->getObject(
                            \Amasty\ShopbyBrand\Model\UrlBuilder\Adapter::class
                        ),
                        'sort_order' => "10"
                    ]
                ],
                [
                    10,
                    50,
                ]
            ],
        ];
    }
}
