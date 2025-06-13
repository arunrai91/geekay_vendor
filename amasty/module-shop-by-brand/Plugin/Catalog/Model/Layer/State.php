<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Plugin\Catalog\Model\Layer;

use Amasty\ShopbyBrand\Model\BrandResolver;
use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Catalog\Model\Layer\State as MagentoStateModel;

class State
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
     * @param MagentoStateModel $subject
     * @param callable $proceed
     * @param Item $filter
     * @return MagentoStateModel
     */
    public function aroundAddFilter(MagentoStateModel $subject, callable $proceed, $filter)
    {
        if ($this->brandResolver->isBrandFilter($filter->getFilter())) {
            return $subject;
        }
        return $proceed($filter);
    }
}
