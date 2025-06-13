<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Category;

interface CategoryDataInterface
{
    public const ID = 'id';
    public const PATH = 'path';
    public const PARENT_PATH = 'parent_path';
    public const LABEL = 'label';
    public const COUNT = 'count';
    public const PERMISSIONS = 'permissions';

    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @param int $id
     */
    public function setId(int $id): void;

    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @param string $path
     */
    public function setPath(string $path): void;

    /**
     * @return string
     */
    public function getParentPath(): string;

    /**
     * @param string $parentPath
     */
    public function setParentPath(string $parentPath): void;

    /**
     * @return string
     */
    public function getLabel(): string;

    /**
     * @param string $label
     */
    public function setLabel(string $label): void;

    /**
     * @return int
     */
    public function getCount(): int;

    /**
     * @param int $count
     */
    public function setCount(int $count): void;

    /**
     * @return string[]
     */
    public function getPermissions(): array;

    /**
     * @param string[] $permissions
     */
    public function setPermissions(array $permissions): void;
}
