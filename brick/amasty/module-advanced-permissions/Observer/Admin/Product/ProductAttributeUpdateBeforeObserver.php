<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Observer\Admin\Product;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\AuthorizationInterface;
use Amasty\Rolepermissions\Helper\Data;

class ProductAttributeUpdateBeforeObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    private $authorization;

    /**
     * @var Data
     */
    private $helper;

    /**
     * ProductAttributeUpdateBeforeObserver constructor.
     *
     * @param AuthorizationInterface $authorization
     * @param Data $helper
     */
    public function __construct(
        AuthorizationInterface $authorization,
        Data $helper
    ) {
        $this->authorization = $authorization;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->authorization->isAllowed('Amasty_Rolepermissions::save_products')) {
            $this->helper->redirectHome();
        }
    }
}
