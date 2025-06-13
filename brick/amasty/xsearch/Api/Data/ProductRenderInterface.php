<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Api\Data;

interface ProductRenderInterface extends \Magento\Catalog\Api\Data\ProductRenderInterface
{
    /**
     * @return string
     */
    public function getSku(): string;

    /**
     * @param string $sku
     * @return void
     */
    public function setSku(string $sku): void;
}
