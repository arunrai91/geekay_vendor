<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Observer;

use Amasty\ShopbyBase\Helper\FilterSetting;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SetCategoryFilterSettingData implements ObserverInterface
{
    private const CATEGORY_ATTRIBUTE_CODE = 'category_ids';

    /**
     * @var FilterSetting
     */
    private $filterSetting;

    public function __construct(
        FilterSetting $filterSetting
    ) {
        $this->filterSetting = $filterSetting;
    }

    public function execute(Observer $observer): void
    {
        $categoryFilterSetting = $observer->getData('object');
        if ($categoryFilterSetting->getAttributeCode() === self::CATEGORY_ATTRIBUTE_CODE) {
            $categoryFilterSetting->addData($this->filterSetting->getCustomDataForCategoryFilter());
        }
    }
}
