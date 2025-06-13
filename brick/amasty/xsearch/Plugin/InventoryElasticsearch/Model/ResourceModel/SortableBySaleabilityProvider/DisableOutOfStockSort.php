<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Plugin\InventoryElasticsearch\Model\ResourceModel\SortableBySaleabilityProvider;

use Amasty\Xsearch\Model\Config;
use Magento\InventoryElasticsearch\Model\ResourceModel\SortableBySaleabilityProvider;

class DisableOutOfStockSort
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsSortableBySaleability(SortableBySaleabilityProvider $subject, bool $result): bool
    {
        return $this->config->isShowOutOfStockLast();
    }
}
