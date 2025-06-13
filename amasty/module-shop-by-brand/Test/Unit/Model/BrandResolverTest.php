<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopByBrand\Test\Unit\Helper;

use Amasty\ShopbyBrand\Model\BrandResolver;
use Amasty\ShopbyBrand\Model\ConfigProvider;
use Amasty\ShopbyBrand\Test\Unit\Traits;

/**
 * Class BrandsPopupTest
 *
 * @see BrandsPopup
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class BrandResolverTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    public const CHECK_ROOT_CATEGORY_VALUE = true;

    public const BRAND_VALUE = '1';

    public const BRAND_ATTRIBUTE_CODE = 'code';

    /**
     * @covers BrandResolver::getCurrentBrand
     */
    public function testGetCurrentBranding()
    {
        $curBranding = $this->getObjectManager()->getObject(\Amasty\ShopbyBase\Model\OptionSetting::class);

        $model = $this->getMockBuilder(BrandResolver::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
        $configProvider = $this->getMockBuilder(ConfigProvider::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBrandAttributeCode'])
            ->getMock();
        $configProvider->expects($this->any())->method('getBrandAttributeCode')
            ->will($this->returnValue(self::BRAND_ATTRIBUTE_CODE));

        $request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getModuleName', 'getParam'])
            ->getMock();
        $request->expects($this->any())->method('getModuleName')
            ->will($this->returnValue('ambrand'));
        $request->expects($this->any())->method('getParam')
            ->will($this->returnValue(self::BRAND_VALUE));

        $store = $this->getObjectManager()->getObject(\Magento\Store\Model\Store::class);
        $store->setData('store_id', 0);

        $storeManager = $this->createMock(\Magento\Store\Model\StoreManager::class);
        $storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $optionHelper = $this->createMock(\Amasty\ShopbyBase\Helper\OptionSetting::class);
        $optionHelper->expects($this->any())->method('getSettingByValue')
            ->will($this->returnValue($curBranding));

        $this->setProperty($model, 'request', $request, BrandResolver::class);
        $this->setProperty($model, 'configProvider', $configProvider, BrandResolver::class);
        $this->setProperty($model, 'optionHelper', $optionHelper, BrandResolver::class);
        $this->setProperty($model, 'storeManager', $storeManager, BrandResolver::class);

        $this->assertInstanceOf(
            \Amasty\ShopbyBase\Api\Data\OptionSettingInterface::class,
            $model->getCurrentBrand(),
            'Getting of branding failed'
        );
    }
}
