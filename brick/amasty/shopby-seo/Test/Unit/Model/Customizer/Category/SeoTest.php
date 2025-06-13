<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Test\Model\Customizer\Category;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Amasty\ShopbySeo\Test\Unit\Traits;
use Amasty\ShopbySeo\Model\Customizer\Category\Seo;

/**
 * Class SeoTest
 *
 * @see Seo
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class SeoTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    public const ROOT_CATEGORY_ID = 2;
    public const BASE_URL = 'http://some-base-url/';
    public const CURRENT_URL = 'http://some-test/test.html?some-param=1';
    public const ROOT_URL = 'http://some-test/all-products';
    public const WITHOUT_GET = 'http://some-test/test.html';

    /**
     * @var Seo
     */
    private $model;

    /**
     * @var MockObject|\Amasty\ShopbySeo\Model\ConfigProvider
     */
    protected $configProvider;

    /**
     * @var MockObject|\Amasty\Shopby\Model\Request
     */
    protected $amshopbyRequest;

    /**
     * @var MockObject|\Magento\Catalog\Model\Category
     */
    protected $rootCategory;

    /**
     * @var MockObject|\Amasty\ShopbyBase\Model\Category\Manager
     */
    protected $categoryManager;

    /**
     * @var MockObject|\Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    protected function setUp(): void
    {
        $this->urlBuilder = $this->createMock(\Amasty\ShopbyBase\Model\UrlBuilder::class);
        $layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $this->configProvider = $this->createMock(\Amasty\ShopbySeo\Model\ConfigProvider::class);
        $this->amshopbyRequest = $this->createMock(\Amasty\Shopby\Model\Request::class);
        $block = $this->createMock(\Magento\LayeredNavigation\Block\Navigation::class);
        $this->categoryManager = $this->createMock(\Amasty\ShopbyBase\Model\Category\Manager::class);

        $this->rootCategory = $this->getMockBuilder(
            \Magento\Catalog\Model\Category::class)
            ->onlyMethods(['getId', 'getUrl'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->categoryManager->method('getRootCategoryId')->willReturn(self::ROOT_CATEGORY_ID);
        $this->categoryManager->method('getBaseUrl')->willReturn(self::BASE_URL);
        $this->rootCategory->method('getId')->willReturn(self::ROOT_CATEGORY_ID);
        $this->urlBuilder->expects($this->any())->method('getCurrentUrl')->willReturn(self::CURRENT_URL);
        $layout->expects($this->any())->method('getAllBlocks')->willReturn([$block]);

        $this->model = $this->getObjectManager()->getObject(
            Seo::class,
            [
                'categoryManager' => $this->categoryManager,
                'configProvider' => $this->configProvider,
                'url' => $this->urlBuilder,
                'layout' => $layout,
                'amshopbyRequest' => $this->amshopbyRequest
            ]
        );
    }

    /**
     * @covers Seo::getRootModeCanonical
     */
    public function testRootCanonicalModeDefault()
    {
        $this->amshopbyRequest->expects($this->any())->method('getRequestParams')->willReturn('test');
        $this->assertEquals(self::CURRENT_URL, $this->model->getRootModeCanonical());
    }

    /**
     * @covers Seo::getRootModeCanonical
     */
    public function testRootCanonicalModeForRootCurrent()
    {
        $this->configProvider->expects($this->any())->method('getCanonicalRoot')->willReturn('root_current');
        $this->assertEquals(self::CURRENT_URL, $this->model->getRootModeCanonical());
    }

    /**
     * @covers Seo::getRootModeCanonical
     */
    public function testRootCanonicalModeForRootPure()
    {
        $this->urlBuilder->expects($this->any())->method('getUrl')->willReturn(self::ROOT_URL);
        $this->configProvider->expects($this->any())->method('getCanonicalRoot')->willReturn('root_pure');
        $this->assertEquals(self::ROOT_URL, $this->model->getRootModeCanonical());
    }

    /**
     * @covers Seo::getRootModeCanonical
     */
    public function testRootCanonicalModeForFirstAttr()
    {
        $this->urlBuilder->expects($this->any())->method('getUrl')->willReturn(self::CURRENT_URL);
        $this->configProvider->expects($this->any())->method('getCanonicalRoot')->willReturn('root_first_attribute');
        $this->assertEquals(self::CURRENT_URL, $this->model->getRootModeCanonical());
    }

    /**
     * @covers Seo::getRootModeCanonical
     */
    public function testRootCanonicalModeWithoutGet()
    {
        $this->configProvider->expects($this->any())->method('getCanonicalRoot')->willReturn('root_cut_off_get');
        $this->assertEquals(self::WITHOUT_GET, $this->model->getRootModeCanonical());
    }

    /**
     * @covers Seo::getCategoryModeCanonical
     */
    public function testGetCategoryModeCanonicalDefault()
    {
        $category = $this->createMock(\Magento\Catalog\Model\Category::class);
        $category->expects($this->any())->method('getUrl')->willReturn(self::CURRENT_URL);
        $this->assertEquals(self::CURRENT_URL, $this->model->getCategoryModeCanonical($category));
    }

    /**
     * @covers Seo::getCategoryModeCanonical
     */
    public function testGetCategoryModeCanonicalCurrent()
    {
        $this->configProvider->expects($this->any())->method('getCanonicalCategory')->willReturn('category_current');
        $category = $this->createMock(\Magento\Catalog\Model\Category::class);
        $this->assertEquals(self::CURRENT_URL, $this->model->getCategoryModeCanonical($category));
    }

    /**
     * @covers Seo::getCategoryModeCanonical
     */
    public function testGetCategoryModeCanonicalPure()
    {
        $this->configProvider->expects($this->any())->method('getCanonicalCategory')->willReturn('category_pure');
        $category = $this->createMock(\Magento\Catalog\Model\Category::class);
        $category->expects($this->any())->method('getUrl')->willReturn(self::ROOT_URL);
        $this->assertEquals(self::ROOT_URL, $this->model->getCategoryModeCanonical($category));
    }

    /**
     * @covers Seo::getCategoryModeCanonical
     */
    public function testGetCategoryModeCanonicalWithoutGet()
    {
        $this->configProvider->expects($this->any())->method('getCanonicalCategory')->willReturn('category_cut_off_get');
        $category = $this->createMock(\Magento\Catalog\Model\Category::class);
        $category->expects($this->any())->method('getUrl')->willReturn(self::CURRENT_URL);
        $this->assertEquals(self::WITHOUT_GET, $this->model->getCategoryModeCanonical($category));
    }
}
