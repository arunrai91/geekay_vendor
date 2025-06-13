<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Test\Unit\Helper;

use Amasty\ShopbySeo\Helper\Url;
use Amasty\ShopbySeo\Test\Unit\Traits;
use Magento\Framework\Registry;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class Url
 *
 * @see Url
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class UrlTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    public const TEST_RIGHT_URL = 'http://testsite.com/testurl';

    public const TEST_QUERY = '?tesquery1=1&testquery2=2';

    public const TEST_WRONG_URL = 'http://testsite.com/media/testurl';

    public const SEO_SUFFIX = '_suf';
    /**
     * @var Url|MockObject
     */
    private $url;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    public function setUp(): void
    {
        $this->url = $this->getMockBuilder(Url::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSeoSuffix', 'isSeoUrlEnabled'])
            ->getMockForAbstractClass();

        $this->request = $this->getObjectManager()
            ->getObject(\Magento\Framework\App\Request\Http::class);

        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $store->expects($this->any())->method('getBaseUrl')->willReturn('http://testsite.com');

        $storeManager = $this->createMock(\Magento\Store\Model\StoreManager::class);
        $storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $dataPersistor = $this->createMock(\Magento\Framework\App\Request\DataPersistor::class);
        $dataPersistor->expects($this->any())->method('get')->willReturn(0);

        $scopeConfig = $this->createMock(\Magento\Framework\App\Config::class);
        $scopeConfig->expects($this->any())->method('isSetFlag')->willReturn(true);

        $this->setProperty($this->url, '_request', $this->request, Url::class);
        $this->setProperty($this->url, 'storeManager', $storeManager, Url::class);
        $this->setProperty($this->url, 'dataPersistor', $dataPersistor, Url::class);
        $this->setProperty($this->url, 'scopeConfig', $scopeConfig, Url::class);
    }

    /**
     * @covers Url::seofyUrl
     * @dataProvider seofyUrlDataProvider
     */
    public function testSeofyUrl($url, $moduleName, $expected)
    {
        $this->request->setModuleName($moduleName);
        $this->url->expects($this->any())->method('getSeoSuffix')->willReturn(self::SEO_SUFFIX);
        $this->url->expects($this->any())->method('isSeoUrlEnabled')->willReturn(false);
        $registryMock = $this->createMock(Registry::class);
        $registryMock->expects($this->any())->method('registry')->willReturnMap([
            ['current_category', null]
        ]);
        $this->setProperty($this->url, 'coreRegistry', $registryMock, Url::class);

        $result = $this->url->seofyUrl($url);
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers Url::removeCategorySuffix
     * @dataProvider removeCategorySuffixDataProvider
     */
    public function testRemoveCategorySuffix($url, $expected)
    {
        $this->url->expects($this->any())->method('getSeoSuffix')->willReturn(self::SEO_SUFFIX);

        $result = $this->url->removeCategorySuffix($url);
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for seofyUrl test
     * @return array
     */
    public function seofyUrlDataProvider()
    {
        return [
            [
                self::TEST_RIGHT_URL,
                'testmod',
                self::TEST_RIGHT_URL
            ],
            [
                self::TEST_WRONG_URL,
                'amshopby',
                self::TEST_WRONG_URL
            ],
            [
                self::TEST_RIGHT_URL . self::TEST_QUERY,
                'catalog',
                self::TEST_RIGHT_URL . self::TEST_QUERY
            ],
            [
                self::TEST_RIGHT_URL . self::SEO_SUFFIX . self::TEST_QUERY,
                'catalog',
                self::TEST_RIGHT_URL . self::SEO_SUFFIX . self::TEST_QUERY
            ]
        ];
    }

    /**
     * Data provider for removeCategorySuffix test
     * @return array
     */
    public function removeCategorySuffixDataProvider()
    {
        return [
            [self::TEST_RIGHT_URL, self::TEST_RIGHT_URL],
            [self::TEST_RIGHT_URL . self::SEO_SUFFIX, self::TEST_RIGHT_URL]
        ];
    }
}
