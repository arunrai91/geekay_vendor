<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Plugin;

use Amasty\ShopbyBrand\Model\BrandResolver;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;

class AttributeFilterPlugin
{
    /**
     * @var BrandResolver
     */
    private $brandResolver;

    public function __construct(BrandResolver $brandResolver)
    {
        $this->brandResolver = $brandResolver;
    }

    /**
     * @param AbstractFilter $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsVisibleWhenSelected(AbstractFilter $subject, $result)
    {
        return ($result && $this->brandResolver->isBrandFilter($subject)) ? false : $result;
    }

    /**
     * @param AbstractFilter $subject
     * @param bool $result
     * @return bool
     */
    public function afterShouldAddState(AbstractFilter $subject, $result)
    {
        return ($result && $this->brandResolver->isBrandFilter($subject)) ? false : $result;
    }
}
