<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Api;

interface RuleProviderInterface
{
    /**
     * @param int $ruleId
     *
     * @return \Amasty\Rules\Model\Rule
     */
    public function getAmruleByRuleId($ruleId);
}
