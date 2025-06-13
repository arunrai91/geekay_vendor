<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Observer;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Api\Logging\MetadataInterfaceFactory;
use Amasty\AdminActionsLog\Logging;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;

class HandleActionPredispatch implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

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
        Logging\ActionFactory $actionFactory,
        MetadataInterfaceFactory $metadataFactory
    ) {
        $this->request = $request;
        $this->actionFactory = $actionFactory;
        $this->metadataFactory = $metadataFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $metadata = $this->metadataFactory->create([
            'request' => $this->request,
            'eventName' => MetadataInterface::EVENT_DISPATCH
        ]);
        $actionHandler = $this->actionFactory->create($metadata, Logging\ActionFactory::FETCH_ANY);
        $actionHandler->execute();
    }
}
