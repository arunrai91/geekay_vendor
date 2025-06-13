<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Plugin;

use Amasty\Rolepermissions\Plugin\Store\Model\WebsiteRepository;

class StoreManager
{
    public const AM_SKIP_STORES_PLUGIN = 'skip_get_stores_plugin';

    /**
     * @var \Amasty\Rolepermissions\Helper\Data
     */
    private $helper;

    /**
     * @var bool
     */
    private $checkForSingleStore = false;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    public function __construct(
        \Magento\Framework\App\State $appState,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        \Amasty\Rolepermissions\Helper\Data $helperData
    ) {
        $this->appState = $appState;
        $this->registry = $registry;
        $this->objectManager = $objectManager;
        $this->request = $request;
        $this->helper = $helperData;
    }

    public function aroundGetStores(
        \Magento\Store\Model\StoreManager $subject,
        \Closure $proceed,
        $withDefault = false,
        $codeKey = false
    ) {
        $rule = $this->registry->registry('current_amrolepermissions_rule');

        if (!$rule
            || $this->checkForSingleStore
            || ($this->appState->getAreaCode() != \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE)
            || $this->helper->canSkipObjectRestriction()
            || $this->registry->registry(self::AM_SKIP_STORES_PLUGIN)
        ) {
            return $proceed($withDefault, $codeKey);
        }

        $allowedStores = $rule->getScopeStoreviews();
        $isAttributesEdit = $this->checkAttributesEdit();

        if ($allowedStores && !$isAttributesEdit) {
            $withDefault = false;
        }

        $result = $proceed($withDefault, $codeKey);

        if ($allowedStores && !$isAttributesEdit) {
            foreach ($result as $key => $store) {
                if (!in_array($store->getId(), $allowedStores)) {
                    unset($result[$key]);
                }
            }
        }

        reset($result);

        return $result;
    }

    public function checkAttributesEdit()
    {
        $postData = $this->request->getPost();

        return $this->request->getModuleName() === 'catalog'
            && (($this->request->getActionName() === 'edit' && isset($postData['attribute_id']))
                || ($this->request->getActionName() === 'createOptions' && isset($postData['options'])));
    }

    public function aroundGetWebsites(
        \Magento\Store\Model\StoreManager $subject,
        \Closure $proceed,
        $withDefault = false,
        $codeKey = false
    ) {
        $rule = $this->registry->registry('current_amrolepermissions_rule');

        if (!$rule
            || $this->registry->registry(WebsiteRepository::AM_USE_ALL_WEBSITES)
            || $this->appState->getAreaCode() != \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
            || $this->helper->canSkipObjectRestriction()
        ) {
            return $proceed($withDefault, $codeKey);
        }

        $allowedStores = $rule->getScopeStoreviews();

        if ($allowedStores) {
            $withDefault = false;
        }

        $result = $proceed($withDefault, $codeKey);

        if ($allowedStores) {
            $allowedWebsites = $rule->getPartiallyAccessibleWebsites();

            foreach ($result as $key => $website) {
                if (!in_array($website->getId(), $allowedWebsites)) {
                    unset($result[$key]);
                }
            }
        }

        reset($result);

        return $result;
    }

    public function aroundHasSingleStore(
        \Magento\Store\Model\StoreManager $subject,
        \Closure $proceed
    ) {
        $this->checkForSingleStore = true;
        $result = $proceed();
        $this->checkForSingleStore = false;

        return $result;
    }
}
