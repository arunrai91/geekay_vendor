<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\ViewModel\Related;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class ShiftResolverViewModel implements ArgumentInterface
{
    /**
     * @var int[] ['position' => shiftValue, ...]
     */
    private $shift = [];

    /**
     * @var int[] ['position' => countDisplayedBlocksValue, ...]
     */
    private $countDisplayedBlocks = [];

    public function getShift(string $position): ?int
    {
        $countDisplayedBlocks = $this->countDisplayedBlocks[$position] ?? 0;
        if ($countDisplayedBlocks >= $this->getMaxDisplayedBlockCount($position)) {
            return null;
        }

        if (!isset($this->shift[$position])) {
            $this->shift[$position] = 0;
        }

        return $this->shift[$position]++;
    }

    public function incrementDisplayedBlockCount(string $position): void
    {
        if (!isset($this->countDisplayedBlocks[$position])) {
            $this->countDisplayedBlocks[$position] = 0;
        }

        $this->countDisplayedBlocks[$position]++;
    }

    /**
     * @api
     *
     * @return int Max count of displayed blocks on one position.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getMaxDisplayedBlockCount(string $position): int
    {
        return 1;
    }
}
