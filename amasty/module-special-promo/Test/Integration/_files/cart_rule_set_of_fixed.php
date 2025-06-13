<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

use Amasty\Rules\Helper\Data;
use Amasty\Rules\Model\ResourceModel\Rule as AmRuleResource;
use Amasty\Rules\Model\Rule as AmRule;
use Magento\Customer\Model\GroupManagement;
use Magento\SalesRule\Model\ResourceModel\Rule as RuleResourceModel;
use Magento\SalesRule\Model\Rule;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var Rule $salesRule */
$salesRule = $objectManager->create(Rule::class);
$salesRule->setData(
    [
        'name' => 'Fixed price for product set',
        'is_active' => 1,
        'customer_group_ids' => [GroupManagement::NOT_LOGGED_IN_ID],
        'coupon_type' => Rule::COUPON_TYPE_NO_COUPON,
        'conditions' => [],
        'simple_action' => Data::TYPE_SETOF_FIXED,
        'discount_amount' => 10,
        'discount_step' => 1,
        'stop_rules_processing' => 0,
        'website_ids' => [
            $objectManager->get(StoreManagerInterface::class)->getWebsite()->getId(),
        ]
    ]
);
$objectManager->get(RuleResourceModel::class)->save($salesRule);

/** @var AmRule $amastyRuleModel */
$amastyRuleModel = $objectManager->create(AmRule::class);
$amastyRuleModel
    ->setData('salesrule_id', $salesRule->getRuleId())
    ->setApplyDiscountTo('asc')
    ->setPriceselector(0)
    ->setNqty(0)
    ->setSkipRule('');

$objectManager->create(AmRuleResource::class)->save($amastyRuleModel);
