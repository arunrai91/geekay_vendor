<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Plugin\Inventory\Model\SourceItem\Command;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Api\Logging\MetadataInterfaceFactory;
use Amasty\AdminActionsLog\Logging\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Inventory\Model\SourceItem\Command\SourceItemsSave;

class SourceItemsSavePlugin
{
    /**
     * @var array
     */
    protected $sourceItems = [];

    /**
     * @var MetadataInterfaceFactory
     */
    private $metadataFactory;

    /**
     * @var ActionFactory
     */
    private $actionFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        MetadataInterfaceFactory $metadataFactory,
        ActionFactory $actionFactory,
        RequestInterface $request
    ) {
        $this->metadataFactory = $metadataFactory;
        $this->actionFactory = $actionFactory;
        $this->request = $request;
    }

    public function beforeExecute(
        SourceItemsSave $subject,
        array $sourceItems
    ): void {
        $this->sourceItems = $sourceItems;
        $this->log();
    }

    public function afterExecute(SourceItemsSave $subject): void
    {
        $this->log(false);
        $this->sourceItems = [];
    }

    private function log(bool $isBefore = true): void
    {
        foreach ($this->sourceItems as $sourceItem) {
            $this->executeLoggingAction($sourceItem, $isBefore);
        }
    }

    private function executeLoggingAction($loggingObject, bool $isBefore = true): void
    {
        $eventName = $isBefore
            ? MetadataInterface::EVENT_SAVE_BEFORE
            : MetadataInterface::EVENT_SAVE_AFTER;

        $metadata = $this->metadataFactory->create([
            'request' => $this->request,
            'eventName' => $eventName,
            'loggingObject' => $loggingObject
        ]);

        $actionHandler = $this->actionFactory->create($metadata);
        $actionHandler->execute();
    }
}
