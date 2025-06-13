<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Pro for Magento 2
 */

namespace Amasty\RulesPro\Plugin\SalesRule\Model\CouponUsageConsumer;

use Amasty\RulesPro\Model\RuleUsageRepository;
use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\SalesRule\Model\CouponUsageConsumer;

class ResetUsageRulesData
{
    /**
     * @var RuleUsageRepository
     */
    private RuleUsageRepository $ruleUsageRepository;

    public function __construct(RuleUsageRepository $ruleUsageRepository)
    {
        $this->ruleUsageRepository = $ruleUsageRepository;
    }

    /**
     * @param CouponUsageConsumer $subject
     * @param OperationInterface $operation
     * @return array
     */
    public function beforeProcess(CouponUsageConsumer $subject, OperationInterface $operation): array
    {
        $this->ruleUsageRepository->_resetState();

        return [$operation];
    }
}
