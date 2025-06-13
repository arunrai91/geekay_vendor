<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Plugin\Framework\Module\Status;

use Amasty\Rules\Model\ResourceModel\Rule;
use Magento\Framework\Module\Status;

class DisableRules
{
    public const MODULE_NAME = 'Amasty_Rules';

    /**
     * @var Rule
     */
    private $ruleResource;

    public function __construct(Rule $ruleResource)
    {
        $this->ruleResource = $ruleResource;
    }

    /**
     * @param Status $subject
     * @param bool $isEnabled
     * @param string[] $modules
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSetIsEnabled(Status $subject, bool $isEnabled, array $modules): array
    {
        if (!$isEnabled && in_array(self::MODULE_NAME, $modules)) {
            $this->ruleResource->disableSpecialPromoRules();
        }

        return [$isEnabled, $modules];
    }
}
