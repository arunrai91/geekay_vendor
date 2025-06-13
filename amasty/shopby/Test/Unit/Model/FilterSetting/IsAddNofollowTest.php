<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Test\Unit\Model\FilterSetting;

use Amasty\ShopbyBase\Model\FilterSetting;
use Amasty\ShopbyBase\Model\FilterSetting\IsAddNofollow;
use Amasty\ShopbyBase\Model\Integration\Shopby\GetSelectedFiltersSettings;
use Amasty\ShopbyBase\Model\Integration\Shopby\IsBrandPage;
use Amasty\ShopbyBase\Model\Integration\ShopbySeo\GetConfigProvider;
use Amasty\ShopbyBase\Model\Integration\ShopbySeo\Request\GetRobotsRegistry;
use Amasty\ShopbyBase\Test\Unit\Traits;
use Amasty\ShopbySeo\Model\ConfigProvider;
use Amasty\ShopbySeo\Model\Request\RobotsRegistry;
use Amasty\ShopbySeo\Model\Source\IndexMode;
use Amasty\ShopbySeo\Model\Source\RelNofollow;

/**
 * Class IsAddNofollowTest
 *
 * @see IsAddNofollow
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class IsAddNofollowTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ReflectionTrait;
    use Traits\ObjectManagerTrait;

    /**
     * @covers FilterSetting::isAddNofollow
     *
     * @dataProvider executeDataProvider
     *
     * @throws \ReflectionException
     */
    public function testExecute(
        int $relNofollow,
        int $followMode,
        bool $enableRelNofollow,
        string $isPageNofollow,
        bool $expectedResult
    ) {
        $getConfigProvider = $this->createMock(GetConfigProvider::class);
        $configProvider = $this->createMock(ConfigProvider::class);
        $configProvider->expects($this->any())->method('isEnableRelNofollow')->willReturn($enableRelNofollow);
        $getConfigProvider->expects($this->any())->method('execute')->willReturn($configProvider);

        $getRobotsRegistry = $this->createMock(GetRobotsRegistry::class);
        $robotsRegistry = $this->createMock(RobotsRegistry::class);
        $robotsRegistry->expects($this->any())->method('get')->willReturn($isPageNofollow);
        $getRobotsRegistry->expects($this->any())->method('execute')->willReturn($robotsRegistry);

        $getSelectedFiltersSettings = $this->createMock(GetSelectedFiltersSettings::class);
        $isBrandPage = $this->createMock(IsBrandPage::class);
        $model = new IsAddNofollow(
            $getConfigProvider,
            $getSelectedFiltersSettings,
            $isBrandPage,
            $getRobotsRegistry
        );

        $actualResult = $model->execute($relNofollow, $followMode);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Data provider for isNofollowByModeDataProvider test
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            [
                RelNofollow::MODE_NO,
                IndexMode::MODE_NEVER,
                true,
                'test',
                false
            ],
            [
                RelNofollow::MODE_NO,
                IndexMode::MODE_SINGLE_ONLY,
                true,
                'test',
                false
            ],
            [
                RelNofollow::MODE_NO,
                IndexMode::MODE_NEVER,
                false,
                'test',
                false
            ],
            [
                RelNofollow::MODE_AUTO,
                IndexMode::MODE_SINGLE_ONLY,
                false,
                'test',
                false
            ],
            [
                RelNofollow::MODE_AUTO,
                IndexMode::MODE_SINGLE_ONLY,
                true,
                'nofollow',
                true
            ],
            [
                RelNofollow::MODE_AUTO,
                IndexMode::MODE_SINGLE_ONLY,
                true,
                'relnofollow',
                true
            ],
            [
                RelNofollow::MODE_AUTO,
                IndexMode::MODE_SINGLE_ONLY,
                false,
                'relnofollow',
                false
            ],
            [
                RelNofollow::MODE_AUTO,
                IndexMode::MODE_NEVER,
                true,
                'rel',
                true
            ],
            [
                RelNofollow::MODE_AUTO,
                IndexMode::MODE_ALWAYS,
                true,
                'rel',
                false
            ],
        ];
    }
}
