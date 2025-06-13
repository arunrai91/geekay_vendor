<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\FilterSetting\AttributeConfig;

interface AttributeListProviderInterface
{
    /**
     * Getting list of attribute codes, which can be configured with Amasty Attribute Settings
     * @return array
     */
    public function getAttributeList();
}
