<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Restoring\Entity\RestoreHandler;

use Amasty\AdminActionsLog\Api\Data\LogEntryInterface;
use Amasty\AdminActionsLog\Api\Logging\ObjectDataStorageInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

class OrderAddress extends Common
{
    /**
     * @var ManagerInterface
     */
    private $eventManager;

    public function __construct(
        ObjectManagerInterface $objectManager,
        ObjectDataStorageInterface $dataStorage,
        StoreManagerInterface $storeManager,
        ManagerInterface $eventManager
    ) {
        parent::__construct(
            $objectManager,
            $dataStorage,
            $storeManager
        );

        $this->eventManager = $eventManager;
    }

    public function restore(LogEntryInterface $logEntry, array $logDetails): void
    {
        if (!empty($logDetails)) {
            parent::restore($logEntry, $logDetails);
            /** @var \Magento\Sales\Model\Order\Address $address */
            $address = $this->getModelObject($logEntry, current($logDetails));
            $this->eventManager->dispatch(
                'admin_sales_order_address_update',
                [
                    'order_id' => $address->getParentId()
                ]
            );
        }
    }
}
