<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Model\Validation\AccessMode\Validator;

use Amasty\Rolepermissions\Api\Data\RuleInterface;
use Amasty\Rolepermissions\Block\Adminhtml\Role\Tab\Scope as ScopeTab;
use Amasty\Rolepermissions\Exception\AccessModeValidationException;
use Amasty\Rolepermissions\Model\Validation\AccessMode\ValidatorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Store\Model\StoreManagerInterface;

class Scope implements ValidatorInterface
{
    public const WEBSITES_SCOPE_KEY = 'scope_websites';
    public const STORE_SCOPE_KEY = 'scope_storeviews';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    public function validate(array $data, RuleInterface $rule): void
    {
        if (isset($data[RuleInterface::SCOPE_ACCESS_MODE])
            && $errorMessage = $this->resolveErrorMessage($data, $rule)
        ) {
            throw new AccessModeValidationException($errorMessage);
        }
    }

    private function resolveErrorMessage(array $data, RuleInterface $rule): ?Phrase
    {
        $currentScopeAccessMode = $rule->getScopeAccessMode();
        $scopeAccessModeToSave = $data[RuleInterface::SCOPE_ACCESS_MODE];

        if ($currentScopeAccessMode != ScopeTab::MODE_NONE
            && $scopeAccessModeToSave == ScopeTab::MODE_NONE
        ) {
            return __('You don\'t have access to the \'All Stores\' mode.');
        }

        if ($currentScopeAccessMode == ScopeTab::MODE_VIEW
            && $scopeAccessModeToSave == ScopeTab::MODE_SITE
        ) {
            return  __('You don\'t have access to the \'Site\' mode.');
        }

        if ($currentScopeAccessMode == ScopeTab::MODE_SITE
            && $scopeAccessModeToSave == ScopeTab::MODE_SITE
            && $rule->getScopeWebsites()
        ) {
            $websitesToSave = $data[self::WEBSITES_SCOPE_KEY] ?? [];

            foreach ($websitesToSave as $websiteToSave) {
                if (!in_array($websiteToSave, $rule->getScopeWebsites())) {
                    return  __('You don\'t have access to the website with ID %1.', $websiteToSave);
                }
            }
        }

        if ($currentScopeAccessMode == ScopeTab::MODE_SITE
            && $scopeAccessModeToSave == ScopeTab::MODE_VIEW
            && $rule->getScopeWebsites()
        ) {
            $storeViewsToSave = $data[self::STORE_SCOPE_KEY] ?? [];
            $allowedStoreIds = $this->getAllowedStoreIds($rule->getScopeWebsites());

            foreach ($storeViewsToSave as $storeViewToSave) {
                if (!in_array($storeViewToSave, $allowedStoreIds)) {
                    return  __('You don\'t have access to the store with ID %1.', $storeViewToSave);
                }
            }
        }

        if ($currentScopeAccessMode == ScopeTab::MODE_VIEW
            && $scopeAccessModeToSave == ScopeTab::MODE_VIEW
            && $rule->getScopeStoreviews()
        ) {
            $storeViewsToSave = $data[self::STORE_SCOPE_KEY] ?? [];

            foreach ($storeViewsToSave as $storeViewToSave) {
                if (!in_array($storeViewToSave, $rule->getScopeStoreviews())) {
                    return  __('You don\'t have access to the store with ID %1.', $storeViewToSave);
                }
            }
        }

        return null;
    }

    private function getAllowedStoreIds(array $websiteIds): array
    {
        $allowedStoreIds = [];

        foreach ($websiteIds as $websiteId) {
            try {
                $website = $this->storeManager->getWebsite($websiteId);

                foreach ($website->getStoreIds() as $storeId) {
                    $allowedStoreIds[] = $storeId;
                }
            } catch (LocalizedException $e) {
                continue;
            }
        }

        return $allowedStoreIds;
    }
}
