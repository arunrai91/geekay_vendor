<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Plugin\App\Config;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Api\Logging\MetadataInterfaceFactory;
use Amasty\AdminActionsLog\Logging;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\RequestInterface;

class ValuePlugin
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

    public function beforeSave(Value $subject): Value
    {
        $metadata = $this->metadataFactory->create([
            'request' => $this->request,
            'eventName' => MetadataInterface::EVENT_SAVE_BEFORE,
            'loggingObject' => $subject
        ]);
        $actionHandler = $this->actionFactory->create($metadata);
        $actionHandler->execute();

        return $subject;
    }
}
