<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\Rule;

$objectManager = Bootstrap::getObjectManager();

/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
$searchCriteria = $searchCriteriaBuilder->addFilter('name', 'Fixed price for product set')
    ->create();
/** @var RuleRepositoryInterface $ruleRepository */
$ruleRepository = $objectManager->get(RuleRepositoryInterface::class);
$items = $ruleRepository->getList($searchCriteria)
    ->getItems();
/** @var Rule $salesRule */
$salesRule = array_pop($items);
if ($salesRule !== null) {
    $ruleRepository->deleteById($salesRule->getRuleId());
}
