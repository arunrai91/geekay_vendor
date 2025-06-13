<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Observer\WebapiRest;

use Amasty\Rolepermissions\Exception\AccessDeniedException;
use Amasty\Rolepermissions\Helper\Data as Helper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;

class RestrictOrderAccess implements ObserverInterface
{
    /**
     * @var Helper
     */
    private $helper;

    public function __construct(
        Helper $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @throws AccessDeniedException
     */
    public function execute(Observer $observer)
    {
        /** @var OrderInterface $order */
        $order = $observer->getData('order');
        $rule = $this->helper->currentRule();

        if ($rule
            && $rule->getScopeStoreviews()
            && $order->getStoreId()
            && !in_array($order->getStoreId(), $rule->getScopeStoreviews())
        ) {
            throw new AccessDeniedException(
                __('Access to the order with ID %1 is denied', $order->getId())
            );
        }
    }
}
