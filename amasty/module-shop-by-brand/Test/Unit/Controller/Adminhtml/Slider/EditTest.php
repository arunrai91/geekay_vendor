<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Test\Unit\Controller\Adminhtml\Slider;

use Amasty\ShopbyBrand\Controller\Adminhtml\Slider\Edit;
use Amasty\ShopbyBrand\Test\Unit\Traits;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class EditTest
 *
 * @see Edit
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class EditTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var Edit
     */
    private $controller;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Amasty\ShopbyBase\Helper\OptionSetting
     */
    private $settingHelper;

    /**
     * @var \Amasty\ShopbyBase\Api\Data\OptionSettingInterface
     */
    private $optionSetting;

    public function setup(): void
    {
        $context = $this->createMock(\Magento\Backend\App\Action\Context::class);
        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->settingHelper = $this->createMock(\Amasty\ShopbyBase\Helper\OptionSetting::class);
        $this->optionSetting = $this->createMock(\Amasty\ShopbyBase\Api\Data\OptionSettingInterface::class);

        $this->controller = $this->getObjectManager()->getObject(
            Edit::class,
            [
                'context' => $context,
                'settingHelper' => $this->settingHelper,
            ]
        );
        $this->setProperty($this->controller, '_request', $this->request);
    }

    /**
     * @covers Edit::loadSettingModel
     */
    public function testLoadSettingModel()
    {
        $this->request->expects($this->exactly(3))->method('getParam')
            ->withConsecutive(
                ['attribute_code'],
                ['option_id'],
                ['store', 0]
            )
            ->willReturnOnConsecutiveCalls(
                'test',
                1,
                2
            );
        $optionSetting = $this->getObjectManager()->getObject(\Amasty\ShopbyBase\Model\OptionSetting::class);
        $optionSetting->setId(1);
        $this->settingHelper->expects($this->any())->method('getSettingByOption')->willReturn($optionSetting);
        $result = $this->invokeMethod($this->controller, 'loadSettingModel');
        $this->assertEquals(2, $result->getCurrentStoreId());
    }


    /**
     * @covers Edit::loadSettingModel
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     *
     */
    public function testLoadSettingModelWithoutData()
    {
        $this->request->expects($this->exactly(3))->method('getParam')
            ->withConsecutive(
                ['attribute_code'],
                ['option_id'],
                ['store', 0]
            )
            ->willReturnOnConsecutiveCalls(
                false,
                false,
                false
            );
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);
        $this->invokeMethod($this->controller, 'loadSettingModel');
    }

    /**
     * @covers Edit::loadSettingModel
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     *
     */
    public function testLoadSettingModelWithoutModel()
    {
        $this->settingHelper->expects($this->any())->method('getSettingByValue')->willReturn($this->optionSetting);
        $this->request->expects($this->exactly(3))->method('getParam')
            ->withConsecutive(
                ['attribute_code'],
                ['option_id'],
                ['store', 0]
            )
            ->willReturnOnConsecutiveCalls(
                'test',
                null,
                null
            );
        $this->optionSetting->expects($this->any())->method('getId')->willReturn(0);
        $this->setProperty($this->controller, '_request', $this->request);
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);
        $this->invokeMethod($this->controller, 'loadSettingModel');
    }
}
