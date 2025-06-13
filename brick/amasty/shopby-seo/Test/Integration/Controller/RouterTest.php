<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Test\Integration\Controller;

use Amasty\ShopbySeo\Helper\Data as SeoHelper;
use Amasty\ShopbySeo\Model\SeoOptions;
use Amasty\ShopbySeo\Test\Integration\OptionProcessor;
use Magento\Framework\App\RequestInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    /**
     * @var \Amasty\ShopbySeo\Controller\Router
     */
    private $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var SeoOptions
     */
    private $optionsProvider;

    /**
     * @var SeoHelper
     */
    private $helper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(\Amasty\ShopbySeo\Controller\Router::class);
        $this->optionsProvider = $this->objectManager->get(SeoOptions::class);
        $this->helper = $this->objectManager->get(SeoHelper::class);
    }

    protected function tearDown(): void
    {
        $this->helper->clear();
        parent::tearDown();
    }

    /**
     * @dataProvider withoutAttributeData
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/product_dropdown_attribute.php
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/product_second_dropdown_attribute.php
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/category_anchor.php
     * @magentoConfigFixture default_store amasty_shopby_seo/url/mode 1
     * @magentoConfigFixture default_store amasty_shopby_seo/url/is_generate_seo_default 1
     * @magentoConfigFixture default_store amasty_shopby_seo/url/attribute_name 0
     */
    public function testMatch($uri, $expectedOptions): void
    {
        // recollect storage
        $this->optionsProvider->loadData();
        $request = $this->objectManager->create(RequestInterface::class, ['uri' => $uri]);

        $this->model->match($request);
        $params = $request->getParams();

        $expectedIds = OptionProcessor::processOptionIdTemplateParams($expectedOptions);
        $this->assertEquals($expectedIds, $params, 'Result request params don\'t match expected for URI: ' . $uri);
    }

    public function withoutAttributeData(): array
    {
        return [
            'multi options' => [
                '/category-anchor/option_1-option_2.html',
               ['dropdown_attribute' => '{OPTION_1_ID},{OPTION_2_ID}']
            ],
            'multi options on "all-products" page' => [
                '/option_1-option_2',
                ['dropdown_attribute' => '{OPTION_1_ID},{OPTION_2_ID}']
            ],
            'multi options mixed' => [
                '/category-anchor/option_2-option_1.html',
                ['dropdown_attribute' => '{OPTION_2_ID},{OPTION_1_ID}']
            ],
            'option as same attr' => [
                '/category-anchor/dropdown_attribute.html',
                ['dropdown_attribute' => '{OPTION_AS_ATTR}']
            ],
            'option as different attr' => [
                '/category-anchor/someattr.html',
                ['dropdown_attribute' => '{OPTION_AS_DIF_ATTR}']
            ],
            'multi attributes' => [
                '/category-anchor/option_21-option_3.html',
                [
                    'someattr' => '{OPTION_21_ID}',
                    'dropdown_attribute' => '{OPTION_3_ID}'
                ]
            ],
            'multi attributes with same option' => [
                '/category-anchor/sameoption_1-sameoption.html',
                [
                    'dropdown_attribute' => '{OPTION_AS_DIF_OPT_A1}',
                    'someattr' => '{OPTION_AS_DIF_OPT_A2}',
                ]
            ],
        ];
    }

    /**
     * @dataProvider withAttributeData
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/product_dropdown_attribute.php
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/product_second_dropdown_attribute.php
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/product_dropdown_attribute_alias.php
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/category_anchor.php
     * @magentoConfigFixture default_store amasty_shopby_seo/url/mode 1
     * @magentoConfigFixture default_store amasty_shopby_seo/url/is_generate_seo_default 0
     * @magentoConfigFixture default_store amasty_shopby_seo/url/attribute_name 1
     * @magentoConfigFixture default_store amasty_shopby_seo/url/filter_word filter
     */
    public function testMatchWithAttribute($uri, $expectedOptions): void
    {
        $this->testMatch($uri, $expectedOptions);
    }

    public function withAttributeData(): array
    {
        return [
            'simple option' => [
                '/category-anchor/filter/dropdown_attribute-option_2.html',// input
                ['dropdown_attribute' => '{OPTION_2_ID}'],// expected result
            ],
            'multi options' => [
                '/category-anchor/filter/dropdown_attribute-option_1-option_2.html',
               ['dropdown_attribute' => '{OPTION_1_ID},{OPTION_2_ID}']
            ],
            'multi options on "all-products" page' => [
                '/filter/dropdown_attribute-option_1-option_2',
                ['dropdown_attribute' => '{OPTION_1_ID},{OPTION_2_ID}']
            ],
            'multi options mixed' => [
                '/category-anchor/filter/dropdown_attribute-option_2-option_1.html',
                ['dropdown_attribute' => '{OPTION_2_ID},{OPTION_1_ID}']
            ],
            'option as attr' => [
                '/category-anchor/filter/dropdown_attribute-dropdown_attribute.html',
                ['dropdown_attribute' => '{OPTION_AS_ATTR}']
            ],
            'option as different attr' => [
                '/category-anchor/filter/dropdown_attribute-someattr.html',
                ['dropdown_attribute' => '{OPTION_AS_DIF_ATTR}']
            ],
            'multi attributes' => [
                '/category-anchor/filter/someattr-option_21-dropdown_attribute-someattr.html',
                [
                    'someattr' => '{OPTION_21_ID}',
                    'dropdown_attribute' => '{OPTION_AS_DIF_ATTR}'
                ]
            ],
            'multi attributes reverse' => [
                '/category-anchor/filter/dropdown_attribute-someattr-someattr-option_21.html',
                [
                    'dropdown_attribute' => '{OPTION_AS_DIF_ATTR}',
                    'someattr' => '{OPTION_21_ID}'
                ]
            ],
            'multi attributes with same option' => [
                '/category-anchor/filter/someattr-sameoption-dropdown_attribute-sameoption.html',
                [
                    'someattr' => '{OPTION_AS_DIF_OPT_A2}',
                    'dropdown_attribute' => '{OPTION_AS_DIF_OPT_A1}'
                ]
            ],
            'simple option with alias' => [
                '/category-anchor/filter/attribute_alias-third_attr.html',// input
                ['third_attr' => '{OPTION_ALIAS_ATTR_1}'],// expected result
            ],
        ];
    }

    /**
     * @dataProvider oldAliasData
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/product_dropdown_attribute.php
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/product_second_dropdown_attribute.php
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/product_dropdown_attribute_alias.php
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/category_anchor.php
     * @magentoConfigFixture default_store amasty_shopby_seo/url/mode 1
     * @magentoConfigFixture default_store amasty_shopby_seo/url/is_generate_seo_default 0
     * @magentoConfigFixture default_store amasty_shopby_seo/url/attribute_name 1
     * @magentoConfigFixture default_store amasty_shopby_seo/url/special_char -
     * @magentoConfigFixture default_store amasty_shopby_seo/url/option_separator -
     */
    public function testMatchOldAlias($uri, $expectedOptions): void
    {
        $this->testMatch($uri, $expectedOptions);
    }

    public function oldAliasData(): array
    {
        return [
            'simple option' => [
                '/category-anchor/dropdown_attribute-option-2.html',// input
                ['dropdown_attribute' => '{OPTION_2_ID}'],// expected result
            ],
            'simple option w alias' => [
                '/category-anchor/attribute_alias-third-attr.html',// input
                ['third_attr' => '{OPTION_ALIAS_ATTR_1}'],// expected result
            ],
            'multi options' => [
                '/category-anchor/dropdown_attribute-option-1-option-2.html',
                ['dropdown_attribute' => '{OPTION_1_ID},{OPTION_2_ID}']
            ],
            'multi options on "all-products" page' => [
                '/dropdown_attribute-option-1-option-2.html',
                ['dropdown_attribute' => '{OPTION_1_ID},{OPTION_2_ID}']
            ],
            'multi options mixed' => [
                '/category-anchor/dropdown_attribute-option-2-option-1.html',
                ['dropdown_attribute' => '{OPTION_2_ID},{OPTION_1_ID}']
            ],
            'option as attr' => [
                '/category-anchor/dropdown_attribute-dropdown-attribute.html',
                ['dropdown_attribute' => '{OPTION_AS_ATTR}']
            ],
            'multi attributes with same option' => [
                '/category-anchor/someattr-sameoption-dropdown_attribute-sameoption.html',
                [
                    'someattr' => '{OPTION_AS_DIF_OPT_A2}',
                    'dropdown_attribute' => '{OPTION_AS_DIF_OPT_A1}'
                ]
            ],
        ];
    }

    /**
     * @dataProvider withAttributeAndSeparatorData
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/product_dropdown_attribute.php
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/category_anchor.php
     * @magentoConfigFixture default_store amasty_shopby_seo/url/mode 1
     * @magentoConfigFixture default_store amasty_shopby_seo/url/is_generate_seo_default 0
     * @magentoConfigFixture default_store amasty_shopby_seo/url/attribute_name 1
     * @magentoConfigFixture default_store amasty_shopby_seo/url/special_char -
     * @magentoConfigFixture default_store amasty_shopby_seo/url/option_separator _
     */
    public function testMatchWithAttributeAndSeparatorData($uri, $expectedOptions): void
    {
        $this->testMatch($uri, $expectedOptions);
    }

    public function withAttributeAndSeparatorData(): array
    {
        return [
            'simple option' => [
                '/category-anchor/dropdown-attribute_option-2.html',// input
                ['dropdown_attribute' => '{OPTION_2_ID}'],// expected result
            ],
            'multi options' => [
                '/category-anchor/dropdown-attribute_option-1_option-2.html',
                ['dropdown_attribute' => '{OPTION_1_ID},{OPTION_2_ID}']
            ],
            'multi options mixed' => [
                '/category-anchor/dropdown-attribute_option-2_option-1.html',
                ['dropdown_attribute' => '{OPTION_2_ID},{OPTION_1_ID}']
            ],
            'option as attr' => [
                '/category-anchor/dropdown-attribute_dropdown-attribute.html',
                ['dropdown_attribute' => '{OPTION_AS_ATTR}']
            ],
        ];
    }
}
