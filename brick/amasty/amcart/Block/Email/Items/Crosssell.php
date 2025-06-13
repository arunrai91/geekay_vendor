<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Block\Email\Items;

use Magento\Catalog\Model\Product\LinkFactory as ProductLinkFactory;

class Crosssell extends Link
{
    /**
     * @return ProductLinkFactory
     */
    protected function getLinkModel()
    {
        return $this->productLinkFactory->create()->useCrossSellLinks();
    }
}
