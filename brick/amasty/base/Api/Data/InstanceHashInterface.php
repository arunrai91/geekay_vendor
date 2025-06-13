<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Api\Data;

interface InstanceHashInterface
{
    public const ID = 'id';
    public const CODE = 'code';
    public const VALUE = 'value';
    public const UPDATED_AT = 'updated_at';

    /**
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * @param string $code
     * @return void
     */
    public function setCode(string $code): void;

    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @param string $value
     * @return void
     */
    public function setValue(string $value): void;

    /**
     * @return string|null
     */
    public function getValue(): ?string;

    public function setUpdatedAt(string $updatedAt): void;

    /**
     * @return string
     */
    public function getUpdatedAt(): string;
}
