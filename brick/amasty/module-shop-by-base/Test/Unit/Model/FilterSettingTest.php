<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Test\Unit\Model;

use Amasty\ShopbyBase\Model\FilterSetting;
use Amasty\ShopbyBase\Model\FilterSettingFactory;
use Amasty\ShopbyBase\Test\Unit\Traits;

/**
 * Class FilterSettingTest
 *
 * @see FilterSetting
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class FilterSettingTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ReflectionTrait;
    use Traits\ObjectManagerTrait;

    /**
     * @var FilterSetting
     */
    private $model;

    /**
     * @var \Amasty\ShopbyBase\Model\FilterSettingFactory
     */
    private $filterSettingFactory;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute
     */
    private $attrModel;

    /**
     * @var FilterSettingFactory
     */
    private $filterSettings;

    public function setUp(): void
    {
        $this->model = $this->getObjectManager()->getObject(FilterSetting::class);
        $this->filterSettingFactory = $this
            ->createPartialMock(\Amasty\ShopbyBase\Model\FilterSettingFactory::class, ['create']);
        $this->attrModel = $this->createMock(\Magento\Eav\Model\Entity\Attribute::class);
        $this->filterSettings = $this->getMockBuilder(FilterSettingFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @covers FilterSettingTest::getUnitsLabel
     *
     * @throws \ReflectionException
     */
    public function testGetUnitsLabel()
    {
        $this->assertEquals(null, $this->model->getUnitsLabel());
        $this->model->setData($this->model::USE_CURRENCY_SYMBOL, 'test');
        $this->assertEquals('test', $this->model->getUnitsLabel('test'));
    }
}
