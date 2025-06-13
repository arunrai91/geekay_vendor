<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\DataStorage;

use Amasty\AdminActionsLog\Api\Logging\ObjectDataStorageInterface;
use Magento\Backend\Model\Session;

class BackendSession implements ObjectDataStorageInterface
{
    /**
     * @var Session
     */
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function get(string $key): ?array
    {
        $getterName = 'get' . ucfirst($key);
        //phpcs:ignore
        $storedData = call_user_func([$this->session, $getterName]);

        return is_array($storedData) ? $storedData : [$storedData];
    }

    public function set(string $key, array $data): void
    {
        $setterName = 'set' . ucfirst($key);
        //phpcs:ignore
        call_user_func_array([$this->session, $setterName], [$data]);
    }

    public function unset(string $key): void
    {
        $unsetterName = 'uns' . ucfirst($key);
        //phpcs:ignore
        call_user_func([$this->session, $unsetterName]);
    }

    public function isExists(string $key): bool
    {
        $checkerName = 'has' . ucfirst($key);
        //phpcs:ignore
        return call_user_func([$this->session, $checkerName]);
    }
}
