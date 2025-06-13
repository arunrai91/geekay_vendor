<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Api\Logging;

use Magento\Framework\App\RequestInterface;

interface MetadataInterface
{
    /**
     * Basic event actions identifiers.
     */
    public const EVENT_DISPATCH = 'dispatch';
    public const EVENT_SAVE_BEFORE = 'save_before';
    public const EVENT_SAVE_AFTER = 'save_after';
    public const EVENT_DELETE = 'delete';
    public const EVENT_LOGIN = 'login';
    public const EVENT_LAYOUT_RENDER_BEFORE = 'layout_render_before';

    /**
     * Current action's request.
     *
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface;

    /**
     * Action's event name.
     *
     * @return string
     */
    public function getEventName(): string;

    /**
     * Action's logging object.
     *
     * @return object|null
     */
    public function getObject(): ?object;

    /**
     * Object key. It could be set while executing entities by schedule.
     *
     * @return string|null
     */
    public function getStorageEntityKey(): ?string;
}
