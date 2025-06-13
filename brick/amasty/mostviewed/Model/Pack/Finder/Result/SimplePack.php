<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Pack\Finder\Result;

class SimplePack
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $packQty = 0;

    /**
     * @var array
     */
    private $items = [];

    /**
     * @var array
     */
    private $parentItems = [];

    /**
     * @var ComplexPack
     */
    private $complexPack;

    public function __construct(GetSimplePackId $getSimplePackId)
    {
        $this->id = $getSimplePackId->execute();
    }

    public function addItem(int $itemId, float $qty, bool $isParent = false): void
    {
        if (isset($this->items[$itemId])) {
            $this->items[$itemId] += $qty;
        } else {
            $this->items[$itemId] = $qty;
        }

        if ($isParent) {
            if (isset($this->parentItems[$itemId])) {
                $this->parentItems[$itemId] += $qty;
            } else {
                $this->parentItems[$itemId] = $qty;
            }
        }
    }

    public function getItemQty(int $itemId): float
    {
        return $this->items[$itemId] ?? 0;
    }

    public function getPackQty(): int
    {
        return $this->packQty;
    }

    public function setPackQty(int $packQty): void
    {
        $this->packQty = $packQty;
    }

    public function getItemsCount(bool $excludeParents = false): int
    {
        $result = count($this->items);
        if ($excludeParents) {
            $result -= count($this->parentItems);
        }

        return $result;
    }

    public function setComplexPack(ComplexPack $complexPack): void
    {
        $this->complexPack = $complexPack;
    }

    public function getComplexPack(): ComplexPack
    {
        return $this->complexPack;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
