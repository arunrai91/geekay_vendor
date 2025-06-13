<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Test\Unit\Utils\Text;

use Amasty\Base\Utils\Text\Splitter;
use PHPUnit\Framework\TestCase;

class SplitterTest extends TestCase
{
    /**
     * @var Splitter
     */
    private $splitter;

    public function setUp(): void
    {
        $this->splitter = new Splitter();
    }

    /**
     * @param string $initialText
     * @param int $maxLength
     * @param array $expectedSplit
     * @return void
     * @dataProvider splitDataProvider
     */
    public function testSplit(string $initialText, int $maxLength, array $expectedSplit): void
    {
        $this->assertEquals($expectedSplit, $this->splitter->splitByMaxLength($initialText, $maxLength));
    }

    public function splitDataProvider(): array
    {
        return [
            'empty string' => [
                '',
                10,
                ['', '']
            ],
            'single word with greater maxlength' => [
                'Ololo',
                100,
                ['Ololo', '']
            ],
            'multiple words with greater maxlenght' => [
                'first second third',
                100,
                ['first second third', '']
            ],
            'last word in second part' => [
                'first second third',
                13,
                ['first second', ' third']
            ],
            'split by newline character' => [
                "first second\nthird fourth",
                13,
                ["first second","\nthird fourth"]
            ],
            'split after dot' => [
                'first second. third fourth',
                13,
                ['first', ' second. third fourth']
            ]
        ];
    }
}
