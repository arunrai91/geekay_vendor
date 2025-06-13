<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Utils;

use Magento\Framework\Data\Collection;

class BatchLoader
{
    public const BATCH_SIZE = 100;

    public function execute(Collection $collection, $batchSize = self::BATCH_SIZE)
    {
        $currentPage = 1;
        $collection->setPageSize($batchSize);
        $collection->setCurPage($currentPage);
        $totalPagesCount = $collection->getLastPageNumber();

        while ($currentPage <= $totalPagesCount) {
            $collection->clear();
            $collection->setCurPage($currentPage);

            foreach ($collection->getItems() as $item) {
                yield $item;
            }

            $currentPage++;
        }
    }
}
