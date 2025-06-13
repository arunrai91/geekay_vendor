<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Observer\Admin;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;
use Amasty\Rolepermissions\Model\Rule as AmRule;

class ActionPredispatchObserver implements ObserverInterface
{
    /**
     * @var \Amasty\Rolepermissions\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    private $_coreRegistry;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\CatalogRule\Model\Rule $catalogRule
     */
    private $catalogRule;

    /**
     * @var \Magento\SalesRule\Model\Rule $cartRule
     */
    private $cartRule;

    /**
     * ActionPredispatchObserver constructor.
     *
     * @param \Magento\Framework\Registry                 $registry
     * @param \Amasty\Rolepermissions\Helper\Data         $helper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\CatalogRule\Model\Rule             $catalogRule
     * @param \Magento\SalesRule\Model\Rule               $catRule
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Amasty\Rolepermissions\Helper\Data $helper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\CatalogRule\Model\Rule $catalogRule,
        \Magento\SalesRule\Model\Rule $catRule
    ) {
        $this->_coreRegistry = $registry;
        $this->helper = $helper;
        $this->messageManager = $messageManager;
        $this->catalogRule = $catalogRule;
        $this->cartRule = $catRule;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var RequestInterface $request */
        $request = $observer->getRequest();

        if ($this->isWysiwygImages($request->getControllerName())) {
            return;
        }

        $rule = $this->helper->currentRule();

        if ($rule instanceof AmRule
            && $request->getActionName() === 'edit'
            && $rule->getAttributes()
        ) {
            $conditionWarningMessage = __('There are attributes used in conditions which you don\'t have access to.
            Rule cannot be deleted or saved.');

            if ($request->getRouteName() == 'catalog_rule') {
                /** @var \Magento\CatalogRule\Model\Rule $catalogRule */
                $catalogRule = $this->catalogRule->load($request->getParam('id'));
                $this->_coreRegistry->register('catalog_price_rule', $catalogRule, true);
                if (!$rule->isAttributesInRole($catalogRule, $rule::CATALOG)) {
                    $this->messageManager->addWarningMessage($conditionWarningMessage);
                }
            }

            if ($request->getRouteName() == 'sales_rule') {
                /** @var \Magento\SalesRule\Model\Rule $cartRule */
                $cartRule = $this->cartRule->load($request->getParam('id'));
                $this->_coreRegistry->register('cart_price_rule', $cartRule, true);
                if (!$rule->isAttributesInRole($cartRule, $rule::CART)) {
                    $this->messageManager->addWarningMessage($conditionWarningMessage);
                }
            }
        }

        if ($request->getControllerName() == 'product_set'
            && $request->getActionName() == 'index'
        ) {
            if ($this->helper->restrictAttributeSets()) {
                $this->messageManager->addWarningMessage(__('Attributes required to create an attribute set are
                 restricted'));
            }
        }

        if (!$rule || !$rule->getScopeStoreviews()) {
            return;
        }

        if ($storeId = $request->getParam('store')) {
            if (is_array($storeId)) {
                $storeId = $storeId['store_id'];
            }

            if (!in_array($storeId, $rule->getScopeStoreviews())) {
                $this->helper->redirectHome();
            }
        } elseif ($websiteId = $request->getParam('website') && $this->needToCheck($request->getParam('website'))) {
            if (is_array($websiteId)) {
                $websiteId = $websiteId['website_id'];
            }

            if (!$rule->hasScopeWebsites() || !in_array($websiteId, $rule->getScopeWebsites())) {
                $this->helper->redirectHome();
            }
        } elseif ($group = $request->getParam('group')) {
            if (is_array($group)) {
                $websiteId = $group['website_id'];

                if (!$rule->hasScopeWebsites() || !in_array($websiteId, $rule->getScopeWebsites())) {
                    $this->helper->redirectHome();
                }
            }
        }
    }

    /**
     * Setting should have only ids.
     * If we receive something else, we supposed that is some custom setting and don't need to check permissions.
     *
     *
     * @param array|string $data
     * @return bool
     */
    private function needToCheck($data): bool
    {
        if (is_array($data)) {
            return true;
        }

        return (bool)preg_match('/^\d+(,\d+)*,?$/', $data);
    }

    private function isWysiwygImages($controllerName)
    {
        $isWysiwygImages = false;

        $wysiwygImageControllers = ['wysiwyg_images', 'cms_wysiwyg_images'];
        if (in_array($controllerName, $wysiwygImageControllers)) {
            $isWysiwygImages = true;
        }

        return $isWysiwygImages;
    }
}
