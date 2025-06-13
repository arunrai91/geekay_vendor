<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\ActionType;

use Amasty\AdminActionsLog\Api\Logging\LoggingActionInterface;
use Magento\Framework\Profiler;
use Psr\Log\LoggerInterface;

class Composite implements LoggingActionInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var LoggingActionInterface[]
     */
    private $actions;

    public function __construct(
        LoggerInterface $logger,
        array $actions = []
    ) {
        foreach ($actions as $action) {
            if (!$action instanceof LoggingActionInterface) {
                throw new \LogicException(
                    sprintf('Action handler must implement %s', LoggingActionInterface::class)
                );
            }
        }

        $this->logger = $logger;
        $this->actions = $actions;
    }

    public function execute(): void
    {
        foreach ($this->actions as $action) {
            try {
                $actionTimerName = sprintf('__ACTION_LOG_RUN_%s__', get_class($action));
                Profiler::start($actionTimerName);
                $action->execute();
                Profiler::stop($actionTimerName);
            } catch (\Throwable $e) {
                $this->logger->error(
                    __('An error occurred during Logging Action execution. Error is %1', $e->getMessage())
                );
            }
        }
    }
}
