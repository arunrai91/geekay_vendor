<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Category;

use Magento\Framework\DataObject;

class CategoryData extends DataObject implements CategoryDataInterface
{
    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->getDataByKey(self::ID);
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->setData(self::ID, $id);
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return (string)$this->getDataByKey(self::PATH);
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->setData(self::PATH, $path);
    }

    /**
     * @return string
     */
    public function getParentPath(): string
    {
        return $this->getDataByKey(self::PARENT_PATH);
    }

    /**
     * @param string $parentPath
     */
    public function setParentPath(string $parentPath): void
    {
        $this->setData(self::PARENT_PATH, $parentPath);
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return (string)$this->getDataByKey(self::LABEL);
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label): void
    {
        $this->setData(self::LABEL, $label);
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return (int)$this->getDataByKey(self::COUNT);
    }

    /**
     * @param int $count
     */
    public function setCount(int $count): void
    {
        $this->setData(self::COUNT, $count);
    }

    /**
     * @return array
     */
    public function getPermissions(): array
    {
        return $this->getDataByKey(self::PERMISSIONS);
    }

    /**
     * @param array $permissions
     */
    public function setPermissions(array $permissions): void
    {
        $this->setData(self::PERMISSIONS, $permissions);
    }
}
