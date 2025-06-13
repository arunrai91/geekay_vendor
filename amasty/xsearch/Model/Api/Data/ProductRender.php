<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\Api\Data;

use Amasty\Xsearch\Api\Data\ProductRenderInterface;

class ProductRender extends \Magento\Catalog\Model\ProductRender implements ProductRenderInterface
{
    public function getSku(): string
    {
        return $this->getData('sku');
    }

    public function setSku(string $sku): void
    {
        $this->setData('sku', $sku);
    }
}
