<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Test\Unit\Helper;

use Amasty\Shopby\Model\Layer\Filter\Resolver\FilterRequestDataResolver;
use Amasty\ShopbySeo\Helper\Meta;
use Amasty\ShopbySeo\Test\Unit\Traits;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class Meta
 *
 * @see Meta
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class MetaTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    public const TEST_VALUES = 'test1,test2';

    public const TEST_TAG_VALUE = true;

    /**
     * @var Meta|MockObject
     */
    private $meta;

    /**
     * @var \Amasty\ShopbyBase\Model\FilterSetting
     */
    private $filterSetting;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\FilterInterface|MockObject
     */
    private $filter;

    /**
     * @var \Amasty\Shopby\Helper\Data
     */
    private $dataHelper;

    /**
     * @var FilterRequestDataResolver|null
     */
    private $filterRequestDataResolver;

    public function setUp(): void
    {
        $this->meta = $this->getMockBuilder(Meta::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();

        $this->filterRequestDataResolver = $this->createMock(FilterRequestDataResolver::class);

        $this->filterSetting = $this->getObjectManager()
            ->getObject(\Amasty\ShopbyBase\Model\FilterSetting::class);

        $this->filter = $this->createMock(\Magento\Catalog\Model\Layer\Filter\FilterInterface::class);


        $this->dataHelper = $this->createMock(\Amasty\Shopby\Helper\Data::class);
        $this->dataHelper->expects($this->any())->method('getSelectedFiltersSettings')->willReturn([]);

    }

    /**
     * @covers Meta::getTagByData
     * @dataProvider getTagByDataDataProvider
     */
    public function testGetTagByData($tagKey, $settingMode, $tagValue, $expected)
    {
        $scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->setProperty($this->meta, 'dataHelper', $this->dataHelper, Meta::class);
        $this->filterRequestDataResolver->expects($this->any())->method('getFilterParam')
            ->willReturn(self::TEST_VALUES);
        $scopeConfig->expects($this->any())->method('isSetFlag')->willReturn(true);

        $this->filterSetting->setData($tagKey, $settingMode);
        $data = [
            'setting' => $this->filterSetting,
            'filter' => $this->filter
        ];

        $this->setProperty($this->meta, 'scopeConfig', $scopeConfig, Meta::class);
        $this->setProperty(
            $this->meta,
            'filterRequestDataResolver',
            $this->filterRequestDataResolver,
            Meta::class
        );

        $result = $this->meta->getTagByData($tagKey, $tagValue, $data);
        $this->assertEquals($expected, $result);
    }

    public function getTagByDataDataProvider()
    {
        return [
            ['index_mode', 2, self::TEST_TAG_VALUE, self::TEST_TAG_VALUE],
            ['index_mode', 1, self::TEST_TAG_VALUE, false ],
            ['index_mode', 0, self::TEST_TAG_VALUE, false],
            ['follow_mode', 2, self::TEST_TAG_VALUE, self::TEST_TAG_VALUE]
        ];
    }
}
