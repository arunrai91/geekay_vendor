<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Test\Unit\Model\Ajax;

use Amasty\Shopby\Model\Ajax\UrlAjaxParams;
use PHPUnit\Framework\TestCase;

class UrlAjaxParamsTest extends TestCase
{
    /**
     * @var UrlAjaxParams|\PHPUnit\Framework\MockObject\MockObject
     */
    private $model;

    protected function setUp(): void
    {
        $this->model = $this->createPartialMock(UrlAjaxParams::class, []);
        parent::setUp();
    }

    /**
     * @dataProvider uriProvider
     * @param string[]|string $data
     * @param string[]|string $expectedResult
     * @return void
     */
    public function testRemoveAjaxParam($data, $expectedResult): void
    {
        $result = $this->model->removeAjaxParam($data);
        $this->assertSame($expectedResult, $result);
    }

    public function uriProvider(): array
    {
        return [
            [
                'http://localhost/some-category?shopbyAjax=1',
                'http://localhost/some-category'
            ],
            [
                'http://localhost/some-category?shopbyAjax=1&shopby=0',
                'http://localhost/some-category?shopby=0'
            ],
            [
                'http://localhost/shopbyAjax?ajax=1&shopbyAjax=1',
                'http://localhost/shopbyAjax?ajax=1'
            ],
            [
                'http://localhost/some-category?shopbyAjax=1&shopbyAjax=1',
                'http://localhost/some-category'
            ],
            [
                'http://localhost/some-category?shopbyAjax=1&amp;shopbyAjax=1',
                'http://localhost/some-category'
            ],
            [
                'http://localhost/some-category?ajax=1&amp;shopbyAjax=1&shopbyAjax=1',
                'http://localhost/some-category?ajax=1'
            ],
            [
                [
                    'url?shopbyAjax=1',
                    'url?shopbyAjax=1&shopby=0',
                    'shopbyAjax?ajax=1&shopbyAjax=1',
                    '?shopbyAjax=1&shopbyAjax=1',
                    '?shopbyAjax=1&amp;shopbyAjax=1',
                ],
                [
                    'url',
                    'url?shopby=0',
                    'shopbyAjax?ajax=1',
                    '',
                    '',
                ]
            ],
            [
                'http://localhost/some-category?ajax=1&amp;shopbySorting=1&shopbySorting=1',
                'http://localhost/some-category?ajax=1'
            ]
        ];
    }
}
