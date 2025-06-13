<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Test\Unit\Model\Layer\Filter\Resolver;

use Amasty\Shopby\Model\Layer\Filter\Attribute;
use Amasty\Shopby\Model\Layer\Filter\OnSale;
use Amasty\Shopby\Model\Layer\Filter\Price;
use Amasty\Shopby\Test\Unit\Traits;
use Amasty\ShopbyBase\Model\FilterSetting;
use Amasty\Shopby\Model\Layer\Filter\Resolver\FilterSettingResolver;
use Amasty\ShopbyBase\Model\FilterSetting\FilterResolver;
use Amasty\ShopbyBase\Model\FilterSetting\IsMultiselect;
use Amasty\ShopbyPage\Controller\Adminhtml\Page\Edit;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class FilterSettingReolverTest
 *
 * @see FilterSettingResolver
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class FilterSettingReolverTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var MockObject|FilterSettingResolver
     */
    private $model;

    public function setup(): void
    {
        $this->model = $this->getMockBuilder(FilterSettingResolver::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFilterSetting'])
            ->getMock();
    }

    /**
     * @throws \ReflectionException
     */
    public function testIsMultiselectAllowed(): void
    {
        $filterSetting = $this->createMock(FilterSetting::class);
        $filterResolver = $this->createMock(FilterResolver::class);
        $filterResolver->expects($this->any())->method('resolveByFilter')->willReturn($filterSetting);

        $attributeFilter = $this->getObjectManager()->getObject(Attribute::class);
        $priceFilter = $this->getObjectManager()->getObject(Price::class);
        $onSaleFilter = $this->getObjectManager()->getObject(OnSale::class);

        $this->assertEquals(false, $this->invokeMethod($this->model, 'isMultiselectAllowed', [$onSaleFilter]));

        $this->assertEquals(true, $this->invokeMethod($this->model, 'isMultiselectAllowed', [$priceFilter]));

        $isMultiselect = $this->createMock(IsMultiselect::class);
        $isMultiselect->expects($this->any())->method('execute')->willReturn(true);
        $this->setProperty($this->model, 'isMultiselect', $isMultiselect, FilterSettingResolver::class);
        $this->setProperty($this->model, 'filterResolver', $filterResolver, FilterSettingResolver::class);
        $this->assertEquals(true, $this->invokeMethod($this->model, 'isMultiselectAllowed', [$attributeFilter]));
    }
}
