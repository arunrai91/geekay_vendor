<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Helper;

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBrand\Model\BrandResolver;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * @deprecated usage of helpers is deprecated
 * @see \Amasty\ShopbyBrand\Model\BrandResolver
 */
class Content extends AbstractHelper
{
    /**
     * @var BrandResolver
     */
    private $brandResolver;

    public function __construct(
        Context $context,
        BrandResolver $brandResolver
    ) {
        parent::__construct($context);
        $this->brandResolver = $brandResolver;
    }

    /**
     * Get current Brand.
     *
     * @return null|OptionSettingInterface
     * @deprecated moved to model
     * @see \Amasty\ShopbyBrand\Model\BrandResolver::getCurrentBrand
     */
    public function getCurrentBranding()
    {
        return $this->brandResolver->getCurrentBrand();
    }
}
