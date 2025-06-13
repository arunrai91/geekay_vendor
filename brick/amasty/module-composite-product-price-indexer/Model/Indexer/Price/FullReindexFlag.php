<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Composite Product Price Indexer
 */

namespace Amasty\CompositeProductPriceIndexer\Model\Indexer\Price;

class FullReindexFlag
{
    /**
     * @var bool
     */
    private $flag = false;

    public function isFullReindex(): bool
    {
        return $this->flag;
    }

    public function setIsFullReindex(bool $flag): void
    {
        $this->flag = $flag;
    }
}
