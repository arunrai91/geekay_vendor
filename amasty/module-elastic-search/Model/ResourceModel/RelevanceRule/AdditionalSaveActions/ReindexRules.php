<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\ResourceModel\RelevanceRule\AdditionalSaveActions;

use Amasty\ElasticSearch\Api\Data\RelevanceRuleInterface;
use Amasty\ElasticSearch\Model\Indexer\RelevanceRule\RuleProductProcessor;

class ReindexRules implements CRUDCallbackInterface
{
    /**
     * @var RuleProductProcessor
     */
    private $ruleProductProcessor;

    public function __construct(
        RuleProductProcessor $ruleProductProcessor
    ) {
        $this->ruleProductProcessor = $ruleProductProcessor;
    }

    public function execute(RelevanceRuleInterface $rule): void
    {
        if (!$this->ruleProductProcessor->isIndexerScheduled()) {
            $this->ruleProductProcessor->reindexRow($rule->getId());
        }
    }
}
