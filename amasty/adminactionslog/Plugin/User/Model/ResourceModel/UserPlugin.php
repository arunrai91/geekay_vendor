<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Plugin\User\Model\ResourceModel;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Api\Logging\MetadataInterfaceFactory;
use Amasty\AdminActionsLog\Logging;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event;
use Magento\Framework\Model\AbstractModel;
use Magento\User\Model\ResourceModel\User as UserResource;

class UserPlugin
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Event\Manager
     */
    private $eventManager;

    /**
     * @var Logging\ActionFactory
     */
    private $actionFactory;

    /**
     * @var MetadataInterfaceFactory
     */
    private $metadataFactory;

    public function __construct(
        RequestInterface $request,
        Event\Manager $eventManager,
        Logging\ActionFactory $actionFactory,
        MetadataInterfaceFactory $metadataFactory
    ) {
        $this->request = $request;
        $this->eventManager = $eventManager;
        $this->actionFactory = $actionFactory;
        $this->metadataFactory = $metadataFactory;
    }

    public function beforeDelete(UserResource $subject, AbstractModel $user)
    {
        $metadata = $this->metadataFactory->create([
            'request' => $this->request,
            'eventName' => MetadataInterface::EVENT_DELETE,
            'loggingObject' => $user
        ]);
        $actionHandler = $this->actionFactory->create($metadata);
        $actionHandler->execute();
    }

    public function afterDelete(UserResource $subject, $result, AbstractModel $user)
    {
        if ($result) {
            $this->eventManager->dispatch('model_delete_after', ['object' => $user]);
        }
    }
}
