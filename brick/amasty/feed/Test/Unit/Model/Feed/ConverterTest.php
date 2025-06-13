<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Test\Unit\Model\Feed;

use Amasty\Feed\Api\Data\FeedTemplateInterface;
use Amasty\Feed\Model\Feed\ContentProcessor;
use Amasty\Base\Model\Serializer;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    /**
     * @param string $originalContent
     * @param array $unserializedContent ['content_type' => 'content']
     * @param string $templateType
     * @param array $expectedContent ['content_type' => 'content']
     *
     * @return void
     * @covers ContentProcessor::getContent
     * @dataProvider collectDataProvider
     * /
     */
    public function testGetContent(
        string $originalContent,
        array $unserializedContent,
        string $templateType,
        array $expectedContent
    ) {
        $serializerMock = $this->createMock(Serializer::class);
        $serializerMock->expects($this->once())->method('unserialize')
            ->with($originalContent)->willReturn($unserializedContent);

        $feedTemplateMock = $this->createMock(FeedTemplateInterface::class);
        $feedTemplateMock->expects($this->any())->method('getTemplateContent')->willReturn($originalContent);
        $feedTemplateMock->expects($this->any())->method('getTemplateType')->willReturn($templateType);

        $contentProcessor = new ContentProcessor($serializerMock);
        $result = $contentProcessor->getContent($feedTemplateMock);

        $this->assertEquals($expectedContent, $result);
    }

    private function collectDataProvider(): array
    {
        return [
            'xml_type_with_placeholder' => [
                '{"xml_content":"<sku>{attribute="basic|sku"}</sku>< placeholder: any config >'
                . '<title>{attribute="product|name"}</title>"}',
                [
                    'xml_content' => '<sku>{attribute="basic|sku"}</sku>< placeholder: any config >'
                        . '<title>{attribute="product|name"}</title>'
                ],
                'xml',
                [
                    'xml_content' => '<sku>{attribute="basic|sku"}</sku><title>{attribute="product|name"}</title>'
                ]
            ],
            'csv_type_with_placeholder' => [
                '"csv_field":"[{\"attribute\":\"basic|sku\"},< placeholder: any config >]"',
                [
                    'csv_field' => '[{\"attribute\":\"basic|sku\"},< placeholder: any config >]'
                ],
                'csv',
                [
                    'csv_field' => '[{\"attribute\":\"basic|sku\"},]'
                ]
            ],
            'txt_type_with_placeholder' => [
                '"csv_field":"[{\"attribute\":\"basic|sku\"},< placeholder: any config >]"',
                [
                    'csv_field' => '[{\"attribute\":\"basic|sku\"},< placeholder: any config >]'
                ],
                'csv',
                [
                    'csv_field' => '[{\"attribute\":\"basic|sku\"},]'
                ]
            ],
            'xml_type_with_no_placeholder' => [
                '{"xml_content":"<sku>{attribute="basic|sku"}</sku><title>{attribute="product|name"}</title>"}',
                [
                    'xml_content' => '<sku>{attribute="basic|sku"}</sku><title>{attribute="product|name"}</title>'
                ],
                'xml',
                [
                    'xml_content' => '<sku>{attribute="basic|sku"}</sku><title>{attribute="product|name"}</title>'
                ]
            ],
            'csv_type_with_no_placeholder' => [
                '"csv_field":"[{\"attribute\":\"basic|sku\"}]"',
                [
                    'csv_field' => '[{\"attribute\":\"basic|sku\"}]'
                ],
                'csv',
                [
                    'csv_field' => '[{\"attribute\":\"basic|sku\"}]'
                ]
            ],
            'txt_type_with_no_placeholder' => [
                '"csv_field":"[{\"attribute\":\"basic|sku\"}]"',
                [
                    'csv_field' => '[{\"attribute\":\"basic|sku\"}]'
                ],
                'csv',
                [
                    'csv_field' => '[{\"attribute\":\"basic|sku\"}]'
                ]
            ],
            'not_existed_type_with_placeholder' => [
                '{"any_type_content":"<sku>{attribute="basic|sku"}</sku>< placeholder: any config >'
                . '<title>{attribute="product|name"}</title>"}',
                [
                    'any_type_content' => '<sku>{attribute="basic|sku"}</sku>< placeholder: any config >'
                        . '<title>{attribute="product|name"}</title>'
                ],
                'any_type',
                [
                    'any_type_content' => '<sku>{attribute="basic|sku"}</sku>< placeholder: any config >'
                        . '<title>{attribute="product|name"}</title>'
                ]
            ],
            'not_existed_type_with_no_placeholder' => [
                '{"any_type_content":"<sku>{attribute="basic|sku"}</sku><title>{attribute="product|name"}</title>"}',
                [
                    'any_type_content' => '<sku>{attribute="basic|sku"}</sku><title>{attribute="product|name"}</title>'
                ],
                'any_type',
                [
                    'any_type_content' => '<sku>{attribute="basic|sku"}</sku><title>{attribute="product|name"}</title>'
                ]
            ],
        ];
    }
}
