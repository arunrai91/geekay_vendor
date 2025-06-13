<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model;

use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Model\FilterDataLoader\AdapterInterface;
use Amasty\ShopbyBase\Model\FilterDataLoader\FilterDataLoaderInterface;

class FilterDataLoader
{
    /**
     * @var array
     */
    private $adapters = [];

    public function __construct(array $adapters = [])
    {
        $this->adapters = $adapters;
    }

    /**
     * Method loads custom filters data and writes it to FilterSetting model
     *
     * @param FilterSettingInterface $filterSetting
     * @param string $filterCode
     * @param string $fieldName
     */
    public function load(FilterSettingInterface $filterSetting, string $filterCode, ?string $fieldName = null): void
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter instanceof AdapterInterface
                && $adapter->isApplicable($filterCode)
            ) {
                $adapter->load($filterSetting, $filterCode, $fieldName);
                break;
            }
        }
    }
}
