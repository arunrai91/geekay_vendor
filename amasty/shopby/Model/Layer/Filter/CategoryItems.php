<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Layer\Filter;

class CategoryItems implements \IteratorAggregate, \Countable
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * @var string
     */
    protected $startPath = '';

    /**
     * @var int
     */
    protected $countAllItems = 0;

    /**
     * @param string|null $path
     *
     * @return \Amasty\Shopby\Model\Layer\Filter\Item[]
     */
    public function getItems($path = null)
    {
        if ($path === null) {
            $path = $this->startPath;
        }

        return $this->items[$path] ?? [];
    }

    /**
     * @param string|null $path
     *
     * @return int
     */
    public function getItemsCount($path = null)
    {
        return count($this->getItems($path));
    }

    /**
     * @param string $startPath
     *
     * @return $this
     */
    public function setStartPath($startPath)
    {
        $this->startPath = $startPath;
        return $this;
    }

    /**
     * @return string
     */
    public function getStartPath()
    {
        return $this->startPath;
    }

    /**
     * @param string $path
     * @param object $item
     *
     * @return $this
     */
    public function addItem($path, $item)
    {
        $this->items[$path][] = $item;
        return $this;
    }

    /**
     * Set count of all items
     * @param int $count
     *
     * @return $this
     */
    public function setCount($count)
    {
        $this->countAllItems = $count;
        return $this;
    }

    /**
     * Get count of all items
     *
     * @return int
     */
    public function getCount()
    {
        return $this->countAllItems;
    }

    /**
     * Get all items in one array
     *
     * @return \Amasty\Shopby\Model\Layer\Filter\Item[]
     */
    public function getAllItems()
    {
        return array_merge(...$this->items);
    }

    /**
     * @param $itemId
     * @return array
     */
    public function getParentsAndChildrenByItemId($itemId)
    {
        $parents = [];
        $children = [];
        foreach ($this->items as $path => $items) {
            $currentPath = explode("/", $path);
            $hasChildren = false;
            if (in_array($itemId, $currentPath)) {
                $hasChildren = true;
            }
            foreach ($items as $item) {
                if ($hasChildren) {
                    $children[] = $item->getValue();
                }
                if ($item->getValue() ==  $itemId) {
                    $parents = $currentPath;
                }
            }
        }

        $result = array_merge($parents, $children);
        return $result;
    }

    /**
     * Retrieve count of collection loaded items
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Implementation of \IteratorAggregate::getIterator()
     *
     * @return \Traversable
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function sortOptions(): self
    {
        foreach ($this->items as $path => &$items) {
            usort($items, [$this, 'sortOption']);
        }

        return $this;
    }

    private function sortOption(Item $a, Item $b): int
    {
        return strcmp($a->getOptionLabel(), $b->getOptionLabel());
    }

    /**
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function sortOptionsByCount(): self
    {
        foreach ($this->items as &$items) {
            usort(
                $items,
                static function ($a, $b) {
                    return $b->getCount() <=> $a->getCount();
                }
            );
        }

        return $this;
    }
}
