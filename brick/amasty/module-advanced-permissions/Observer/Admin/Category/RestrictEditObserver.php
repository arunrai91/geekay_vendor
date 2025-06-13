<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Observer\Admin\Category;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;

class RestrictEditObserver implements ObserverInterface
{
    /** @var \Amasty\Rolepermissions\Helper\Data */
    protected $helper;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Amasty\Rolepermissions\Helper\Data $helper,
        \Magento\Framework\AuthorizationInterface $authorization,
        StoreManagerInterface $storeManager
    ) {
        $this->helper = $helper;
        $this->_authorization = $authorization;
        $this->storeManager = $storeManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($id = $observer->getRequest()->getParam('id')) {
            $rule = $this->helper->currentRule();

            if ($rule->getCategories() && !in_array($id, $rule->getCategories())) {
                $this->helper->redirectHome();
            }

            if ($observer->getRequest()->getActionName() == 'delete') {
                if (!$this->_authorization->isAllowed('Amasty_Rolepermissions::delete_categories')) {
                    $this->helper->redirectHome();
                }
            }

            if ($storeId = $observer->getRequest()->getParam('store') && $rule->getScopeWebsites()) {
                $rootCategoryId = $this->storeManager->getStore($storeId)->getRootCategoryId();
                if ($id == $rootCategoryId) {
                    $observer->getRequest()->setParam('id', $rootCategoryId);
                }
            }
        }
    }
}
