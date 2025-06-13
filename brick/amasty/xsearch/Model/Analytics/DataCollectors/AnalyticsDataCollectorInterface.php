<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\Analytics\DataCollectors;

interface AnalyticsDataCollectorInterface
{
    /**
     * The method must return an array of identifiers
     * that can be processed by the data collector
     *
     * @return string[]
     */
    public function getIdentifiers(): array;

    public function collect(array $data): void;
}
