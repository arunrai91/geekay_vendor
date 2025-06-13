<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\ResourceModel\RelevanceRule\AdditionalSaveActions;

use Amasty\ElasticSearch\Api\Data\RelevanceRuleInterface;

interface CRUDCallbackInterface
{
    public function execute(RelevanceRuleInterface $rule): void;
}
