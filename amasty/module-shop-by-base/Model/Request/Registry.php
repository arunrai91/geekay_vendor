<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\Request;

use RuntimeException;

class Registry
{
    /**
     * @var array
     */
    private array $registry = [];

    /**
     * @param string $key
     * @param mixed $value
     * @param bool $graceful
     */
    public function register(string $key, $value, bool $graceful = false): void
    {
        if (isset($this->registry[$key])) {
            if ($graceful) {
                return;
            }
            throw new RuntimeException('Registry key "' . $key . '" already exists');
        }

        $this->registry[$key] = $value;
    }

    /**
     * @return mixed|null
     */
    public function registry(string $key)
    {
        if (isset($this->registry[$key])) {
            return $this->registry[$key];
        }

        return null;
    }

    public function unregister(string $key): void
    {
        if (isset($this->registry[$key])) {
            unset($this->registry[$key]);
        }
    }

    public function _resetState(): void
    {
        $this->registry = [];
    }
}
