<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Composite Product Price Indexer
 */

namespace Amasty\CompositeProductPriceIndexer\Plugin\Catalog\Model\Indexer\Product\Price\Action\Full;

use Amasty\CompositeProductPriceIndexer\Model\Indexer\Price\FullReindexFlag;

class SetFullReindexFlag
{
    /**
     * @var FullReindexFlag
     */
    private $fullReindexFlag;

    public function __construct(FullReindexFlag $fullReindexFlag)
    {
        $this->fullReindexFlag = $fullReindexFlag;
    }

    public function beforeExecute(): void
    {
        $this->fullReindexFlag->setIsFullReindex(true);
    }
}
