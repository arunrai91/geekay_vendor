<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Model\Rule\SkipItemsValidator;

use Amasty\Rules\Model\ConfigModel;
use Amasty\Rules\Model\Rule\Action\Discount\AbstractRule;
use Magento\Customer\Model\GroupManagement;
use Magento\Customer\Model\Session;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Magento\Store\Model\StoreManagerInterface;

class TierPriceValidator implements SkipItemValidatorInterface
{
    public const ALL_WEBSITES = '0';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var ConfigModel
     */
    private $configModel;

    public function __construct(
        StoreManagerInterface $storeManager,
        Session $customerSession,
        ConfigModel $configModel
    ) {
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->configModel = $configModel;
    }

    public function validate(AbstractItem $item, Rule $rule): bool
    {
        $tierPrices = $item->getProduct()->getTierPrice();
        if (!$tierPrices) {
            return false;
        }

        $websiteId = $this->storeManager->getWebsite()->getId();
        $groupId = $this->customerSession->getCustomerGroupId();

        foreach ($tierPrices as $tierPrice) {
            if ((($tierPrice['cust_group'] == $groupId)
                    || (GroupManagement::CUST_GROUP_ALL == $tierPrice['cust_group']))
                && (($websiteId === $tierPrice['website_id']) || (self::ALL_WEBSITES === $tierPrice['website_id']))
                && ($item->getQty() >= $tierPrice['price_qty'])
            ) {
                return true;
            }
        }

        return false;
    }

    public function isNeedToValidate(Rule $rule): bool
    {
        $amrule = $rule->getData(AbstractRule::AMASTY_RULE);
        $useGeneralSkipSettings = $amrule->isEnableGeneralSkipSettings();
        $skipConditions = explode(',', (string)$amrule->getSkipRule());

        return ($useGeneralSkipSettings && $this->configModel->getSkipTierPrice())
            || (!$useGeneralSkipSettings
                && in_array(SkipItemValidatorInterface::TIER_PRICE, $skipConditions, true));
    }
}
