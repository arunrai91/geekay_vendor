<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Test\Integration\Model;

use Amasty\ShopbyBase\Model\UrlBuilder\UrlModifier;
use Amasty\ShopbySeo\Helper\Data as SeoHelper;
use Amasty\ShopbySeo\Model\SeoOptions;
use Amasty\ShopbySeo\Test\Integration\OptionProcessor;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @covers UrlModifier
 * @magentoAppArea frontend
 * @magentoAppIsolation disabled
 * @magentoDbIsolation disabled
 */
class UrlModifierTest extends TestCase
{
    private const BASE_URL = 'http://localhost/index.php/';

    private const CATEGORY_ID = 22;

    /**
     * @var UrlModifier
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
        $this->model = $this->objectManager->get(UrlModifier::class);
        $this->optionsProvider = $this->objectManager->get(SeoOptions::class);
        $this->helper = $this->objectManager->get(SeoHelper::class);
    }

    protected function tearDown(): void
    {
        $this->helper->clear();
        parent::tearDown();
    }

    /**
     * @dataProvider urlData
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/product_dropdown_attribute.php
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/product_second_dropdown_attribute.php
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/category_anchor.php
     * @magentoConfigFixture default_store amasty_shopby_seo/url/mode 1
     * @magentoConfigFixture default_store amasty_shopby_seo/url/is_generate_seo_default 1
     * @magentoConfigFixture default_store amasty_shopby_seo/url/attribute_name 0
     */
    public function testExecute(string $currentUrl, ?string $expectedResult): void
    {
        // recollect storage
        $this->optionsProvider->loadData();

        $currentUrl = OptionProcessor::processStringTemplate($currentUrl);
        $result = $this->model->execute($currentUrl, self::CATEGORY_ID, true);
        $this->assertSame($expectedResult, $result, 'Wrong SEO url for input: ' . $currentUrl);
    }

    public function urlData(): array
    {
        return [
            'no modification' => [
                self::BASE_URL . 'category-anchor/option_2.html',// input
                self::BASE_URL . 'category-anchor/option_2.html',// expected result
            ],
            'no modification not category' => [
                self::BASE_URL . 'name/option_1.html',
                self::BASE_URL . 'name/option_1.html',
            ],
            'not category path' => [
                self::BASE_URL . 'name.html?dropdown_attribute={OPTION_1_ID}',
                self::BASE_URL . 'name/option_1.html'
            ],
            'category path' => [
                self::BASE_URL . 'category-anchor.html?dropdown_attribute={OPTION_2_ID}',
                self::BASE_URL . 'category-anchor/option_2.html'
            ],
            'all products path' => [
                self::BASE_URL . 'all-products?dropdown_attribute={OPTION_2_ID}',
                self::BASE_URL . 'option_2'
            ],
            'multi options' => [
                self::BASE_URL . 'category-anchor.html?dropdown_attribute={OPTION_1_ID},{OPTION_2_ID}',
                self::BASE_URL . 'category-anchor/option_1-option_2.html'
            ],
            'multi options mixed' => [
                self::BASE_URL . 'category-anchor/option_1.html?dropdown_attribute={OPTION_2_ID}',
                self::BASE_URL . 'category-anchor/option_2-option_1.html'
            ],
            'multi options not category' => [
                self::BASE_URL . 'name.html?dropdown_attribute={OPTION_1_ID},{OPTION_2_ID}',
                self::BASE_URL . 'name/option_1-option_2.html'
            ],
            'option as attr' => [
                self::BASE_URL . 'category-anchor.html?dropdown_attribute={OPTION_AS_ATTR}',
                self::BASE_URL . 'category-anchor/dropdown_attribute.html'
            ],
            'multi attributes' => [
                self::BASE_URL . 'category-anchor.html?someattr={OPTION_21_ID}&dropdown_attribute={OPTION_3_ID}',
                self::BASE_URL . 'category-anchor/option_21-option_3.html'
            ],
            'multi attributes with same option' => [
                self::BASE_URL . 'category-anchor.html?someattr={OPTION_AS_DIF_OPT_A2}' .
                '&dropdown_attribute={OPTION_AS_DIF_OPT_A1}',
                self::BASE_URL . 'category-anchor/sameoption_1-sameoption.html'
            ],
            'multi attributes with same option 2' => [
                self::BASE_URL . 'category-anchor.html?someattr={OPTION_UNIQ_RUINER_A2}' .
                '&dropdown_attribute={OPTION_AS_DIF_OPT_A1}',
                self::BASE_URL . 'category-anchor/sameoption_1_1-sameoption.html'
            ],
        ];
    }

    /**
     * @dataProvider urlWithAttributeData
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/product_dropdown_attribute.php
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/product_second_dropdown_attribute.php
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/product_dropdown_attribute_alias.php
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/category_anchor.php
     * @magentoConfigFixture default_store amasty_shopby_seo/url/mode 1
     * @magentoConfigFixture default_store amasty_shopby_seo/url/is_generate_seo_default 0
     * @magentoConfigFixture default_store amasty_shopby_seo/url/attribute_name 1
     * @magentoConfigFixture default_store amasty_shopby_seo/url/filter_word filter
     */
    public function testExecuteWithAttribute(string $currentUrl, ?string $expectedResult): void
    {
        $this->testExecute($currentUrl, $expectedResult);
    }

    public function urlWithAttributeData(): array
    {
        return [
            'no modification' => [
                self::BASE_URL . 'category-anchor/filter/dropdown_attribute-option_2.html',
                self::BASE_URL . 'category-anchor/filter/dropdown_attribute-option_2.html',
            ],
            'no modification not category' => [
                self::BASE_URL . 'name/filter/dropdown_attribute-option_1.html',
                self::BASE_URL . 'name/filter/dropdown_attribute-option_1.html',
            ],
            'not category path' => [
                self::BASE_URL . 'name.html?dropdown_attribute={OPTION_1_ID}&test=1&shopbyAjax=1',
                self::BASE_URL . 'name/filter/dropdown_attribute-option_1.html?test=1&shopbyAjax=1'
            ],
            'category path' => [
                self::BASE_URL . 'category-anchor.html?dropdown_attribute={OPTION_2_ID}',
                self::BASE_URL . 'category-anchor/filter/dropdown_attribute-option_2.html'
            ],
            'with alias' => [
                self::BASE_URL . 'category-anchor.html?third_attr={OPTION_ALIAS_ATTR_1}',
                self::BASE_URL . 'category-anchor/filter/attribute_alias-third_attr.html'
            ],
            'all-products path' => [
                self::BASE_URL . 'all-products?dropdown_attribute={OPTION_2_ID}',
                self::BASE_URL . 'filter/dropdown_attribute-option_2'
            ],
            'multi options' => [
                self::BASE_URL . 'category-anchor.html?dropdown_attribute={OPTION_2_ID},{OPTION_1_ID}',
                self::BASE_URL . 'category-anchor/filter/dropdown_attribute-option_2-option_1.html'
            ],
            'multi options mixed' => [
                self::BASE_URL
                . 'category-anchor/filter/dropdown_attribute-option_1.html?dropdown_attribute={OPTION_2_ID}',
                self::BASE_URL . 'category-anchor/filter/dropdown_attribute-option_2-option_1.html'
            ],
            'option as attr' => [
                self::BASE_URL . 'category-anchor.html?dropdown_attribute={OPTION_AS_ATTR}',
                self::BASE_URL . 'category-anchor/filter/dropdown_attribute-dropdown_attribute.html'
            ],
            'multi attributes with same option' => [
                self::BASE_URL . 'category-anchor.html?someattr={OPTION_AS_DIF_OPT_A2}' .
                '&dropdown_attribute={OPTION_AS_DIF_OPT_A1}',
                self::BASE_URL . 'category-anchor/filter/someattr-sameoption-dropdown_attribute-sameoption.html'
            ],
        ];
    }

    /**
     * @dataProvider urlWithAttributeAndOldAliasData
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
    public function testExecuteWithAttributeAndOldAliasParser(string $currentUrl, ?string $expectedResult): void
    {
        $this->testExecute($currentUrl, $expectedResult);
    }

    public function urlWithAttributeAndOldAliasData(): array
    {
        return [
            'no modification' => [
                self::BASE_URL . 'category-anchor/dropdown_attribute-option-2.html',
                self::BASE_URL . 'category-anchor/dropdown_attribute-option-2.html',
            ],
            'no modification not category' => [
                self::BASE_URL . 'name/dropdown_attribute-option-1.html',
                self::BASE_URL . 'name/dropdown_attribute-option-1.html',
            ],
            'not category path' => [
                self::BASE_URL . 'name.html?dropdown_attribute={OPTION_1_ID}&test=1&shopbyAjax=1',
                self::BASE_URL . 'name/dropdown_attribute-option-1.html?test=1&shopbyAjax=1'
            ],
            'category path' => [
                self::BASE_URL . 'category-anchor.html?dropdown_attribute={OPTION_2_ID}',
                self::BASE_URL . 'category-anchor/dropdown_attribute-option-2.html'
            ],
            'with alias' => [
                self::BASE_URL . 'category-anchor.html?third_attr={OPTION_ALIAS_ATTR_1}',
                self::BASE_URL . 'category-anchor/attribute_alias-third-attr.html'
            ],
            'multi options' => [
                self::BASE_URL . 'category-anchor.html?dropdown_attribute={OPTION_2_ID},{OPTION_1_ID}',
                self::BASE_URL . 'category-anchor/dropdown_attribute-option-2-option-1.html'
            ],
            'multi options mixed' => [
                self::BASE_URL . 'category-anchor/dropdown_attribute-option-1.html?dropdown_attribute={OPTION_2_ID}',
                self::BASE_URL . 'category-anchor/dropdown_attribute-option-2-option-1.html'
            ],
            'option as attr' => [
                self::BASE_URL . 'category-anchor.html?dropdown_attribute={OPTION_AS_ATTR}',
                self::BASE_URL . 'category-anchor/dropdown_attribute-dropdown-attribute.html'
            ],
        ];
    }

    /**
     * @dataProvider urlWithAttributeAndSeparatorData
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/product_dropdown_attribute.php
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/category_anchor.php
     * @magentoConfigFixture default_store amasty_shopby_seo/url/mode 1
     * @magentoConfigFixture default_store amasty_shopby_seo/url/is_generate_seo_default 0
     * @magentoConfigFixture default_store amasty_shopby_seo/url/attribute_name 1
     * @magentoConfigFixture default_store amasty_shopby_seo/url/special_char -
     * @magentoConfigFixture default_store amasty_shopby_seo/url/option_separator _
     */
    public function testExecuteWithAttributeAndSeparator(string $currentUrl, ?string $expectedResult): void
    {
        $this->testExecute($currentUrl, $expectedResult);
    }

    public function urlWithAttributeAndSeparatorData(): array
    {
        return [
            'no modification' => [
                self::BASE_URL . 'category-anchor/dropdown-attribute_option-2.html',
                self::BASE_URL . 'category-anchor/dropdown-attribute_option-2.html',
            ],
            'no modification not category' => [
                self::BASE_URL . 'name/dropdown-attribute-option-1.html',
                self::BASE_URL . 'name/dropdown-attribute-option-1.html',
            ],
            'not category path' => [
                self::BASE_URL . 'name.html?dropdown_attribute={OPTION_1_ID}&test=1&shopbyAjax=1',
                self::BASE_URL . 'name/dropdown-attribute_option-1.html?test=1&shopbyAjax=1'
            ],
            'category path' => [
                self::BASE_URL . 'category-anchor.html?dropdown_attribute={OPTION_2_ID}',
                self::BASE_URL . 'category-anchor/dropdown-attribute_option-2.html'
            ],
            'multi options' => [
                self::BASE_URL . 'category-anchor.html?dropdown_attribute={OPTION_2_ID},{OPTION_1_ID}',
                self::BASE_URL . 'category-anchor/dropdown-attribute_option-2_option-1.html'
            ],
            'multi options mixed' => [
                self::BASE_URL . 'category-anchor/dropdown-attribute_option-1.html?dropdown_attribute={OPTION_2_ID}',
                self::BASE_URL . 'category-anchor/dropdown-attribute_option-2_option-1.html'
            ],
            'option as attr' => [
                self::BASE_URL . 'category-anchor.html?dropdown_attribute={OPTION_AS_ATTR}',
                self::BASE_URL . 'category-anchor/dropdown-attribute_dropdown-attribute.html'
            ],
        ];
    }

    /**
     * @dataProvider urlWithAttributeAndSpecialSeparatorData
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/product_dropdown_attribute.php
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/product_second_dropdown_attribute.php
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/product_dropdown_attribute_alias.php
     * @magentoDataFixture Amasty_ShopbySeo::Test/Integration/_files/category_anchor.php
     * @magentoConfigFixture default_store amasty_shopby_seo/url/mode 1
     * @magentoConfigFixture default_store amasty_shopby_seo/url/is_generate_seo_default 0
     * @magentoConfigFixture default_store amasty_shopby_seo/url/attribute_name 1
     * @magentoConfigFixture default_store amasty_shopby_seo/url/special_char -
     * @magentoConfigFixture default_store amasty_shopby_seo/url/option_separator --
     */
    public function testExecuteWithAttributeAndSpecialSeparator(string $currentUrl, ?string $expectedResult): void
    {
        $this->testExecute($currentUrl, $expectedResult);
    }

    public function urlWithAttributeAndSpecialSeparatorData(): array
    {
        return [
            'category path' => [
                self::BASE_URL . 'category-anchor.html?dropdown_attribute={OPTION_2_ID}',
                self::BASE_URL . 'category-anchor/dropdown_attribute--option-2.html'
            ],
            'multi options' => [
                self::BASE_URL . 'category-anchor.html?dropdown_attribute={OPTION_2_ID},{OPTION_1_ID}',
                self::BASE_URL . 'category-anchor/dropdown_attribute--option-2--option-1.html'
            ],
            'multi options mixed' => [
                self::BASE_URL . 'category-anchor/dropdown_attribute--option-1.html?dropdown_attribute={OPTION_2_ID}',
                self::BASE_URL . 'category-anchor/dropdown_attribute--option-2--option-1.html'
            ],
            'with alias' => [
                self::BASE_URL . 'category-anchor.html?third_attr={OPTION_ALIAS_ATTR_1}',
                self::BASE_URL . 'category-anchor/attribute_alias--third-attr.html'
            ]
        ];
    }
}
