<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Cron;

use Amasty\Acart\Model\RuleQuotesProcessor;

class RefreshHistory
{
    /**
     * @var RuleQuotesProcessor
     */
    private $ruleQuotesProcessor;

    public function __construct(
        RuleQuotesProcessor $ruleQuotesProcessor
    ) {
        $this->ruleQuotesProcessor = $ruleQuotesProcessor;
    }

    public function execute()
    {
        $this->ruleQuotesProcessor->prepareRuleQuotes();
    }
}
