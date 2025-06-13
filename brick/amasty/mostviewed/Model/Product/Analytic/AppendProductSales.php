<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Product\Analytic;

use Amasty\Mostviewed\Model\ResourceModel\Product\Sales\InsertMultiple;

class AppendProductSales
{
    /**
     * @var InsertMultiple
     */
    private $insertMultiple;

    public function __construct(
        InsertMultiple $insertMultiple
    ) {
        $this->insertMultiple = $insertMultiple;
    }

    public function execute(array $data): void
    {
        if (!$data) {
            return;
        }

        $this->insertMultiple->execute($data);
    }
}
