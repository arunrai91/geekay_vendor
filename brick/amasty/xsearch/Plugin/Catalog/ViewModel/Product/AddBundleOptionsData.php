<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Plugin\Catalog\ViewModel\Product;

use Amasty\Xsearch\Model\Di\Wrapper;
use Magento\Bundle\Plugin\Catalog\ViewModel\Product\AddBundleOptionsData as AddBundleOptionsDataOriginal;
use Magento\Catalog\Model\Product;
use Magento\Catalog\ViewModel\Product\OptionsData as Subject;

class AddBundleOptionsData
{
    /**
     * @var Wrapper|AddBundleOptionsDataOriginal
     */
    private $addBundleOptionsDataWrapper;

    public function __construct(Wrapper $addBundleOptionsDataWrapper)
    {
        $this->addBundleOptionsDataWrapper = $addBundleOptionsDataWrapper;
    }

    public function afterGetOptionsData(Subject $subject, array $result, Product $product) : array
    {
        return $this->addBundleOptionsDataWrapper->afterGetOptionsData($subject, $result, $product);
    }
}
