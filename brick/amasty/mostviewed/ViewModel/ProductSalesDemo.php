<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class ProductSalesDemo implements ArgumentInterface
{
    public function getSubscribeUrl(): string
    {
        return 'https://amasty.com/amcustomer/account/products/'
            . '?utm_source=extension&utm_medium=backend&utm_campaign=upgrade_relatedproducts';
    }
}
