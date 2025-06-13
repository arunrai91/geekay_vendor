<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Category;

class CategoryTree
{
    /**
     * @var int
     */
    private $count;

    /**
     * @var string
     */
    private $startPath;

    /**
     * @var CategoryDataInterface[]
     */
    private $categories;

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @param int $count
     */
    public function setCount(int $count): void
    {
        $this->count = $count;
    }

    /**
     * @return string
     */
    public function getStartPath(): string
    {
        return $this->startPath;
    }

    /**
     * @param string $startPath
     */
    public function setStartPath(string $startPath): void
    {
        $this->startPath = $startPath;
    }

    /**
     * @return \Amasty\Shopby\Model\Category\CategoryDataInterface[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @param \Amasty\Shopby\Model\Category\CategoryDataInterface[] $categories
     */
    public function setCategories(array $categories): void
    {
        $this->categories = $categories;
    }
}
