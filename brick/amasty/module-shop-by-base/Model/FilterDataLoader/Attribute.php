<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\FilterDataLoader;

use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Model\ResourceModel\FilterSetting as FilterSettingResource;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;

class Attribute implements AdapterInterface
{
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var FilterSettingResource
     */
    private $resource;

    public function __construct(
        Config $eavConfig,
        FilterSettingResource $resource
    ) {
        $this->eavConfig = $eavConfig;
        $this->resource = $resource;
    }

    public function load(FilterSettingInterface $filterSetting, string $filterCode, ?string $fieldName = null): void
    {
        $this->resource->load($filterSetting, $filterCode, $fieldName);
    }

    public function isApplicable(string $filterCode): bool
    {
        return true;
    }
}
