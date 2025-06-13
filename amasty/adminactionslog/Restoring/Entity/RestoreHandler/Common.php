<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Restoring\Entity\RestoreHandler;

use Amasty\AdminActionsLog\Api\Data\LogDetailInterface;
use Amasty\AdminActionsLog\Api\Data\LogEntryInterface;
use Magento\Store\Model\Store;

class Common extends AbstractHandler
{
    public function restore(LogEntryInterface $logEntry, array $logDetails): void
    {
        if (empty($logDetails)) {
            return;
        }

        $element = $this->getModelObject($logEntry, current($logDetails));
        /** @var LogDetailInterface $logDetail */
        foreach ($logDetails as $logDetail) {
            $oldValue = $logDetail->getOldValue();
            $elementKey = $logDetail->getName();
            $element->setData($elementKey, $oldValue);
        }

        if (!$element->hasData('store_id')) {
            $storeId = $logEntry->getStoreId() === null ? Store::DEFAULT_STORE_ID : $logEntry->getStoreId();

            $element->setData('store_id', $storeId);
        }

        $this->setRestoreActionFlag($element);
        $element->save();
    }
}
