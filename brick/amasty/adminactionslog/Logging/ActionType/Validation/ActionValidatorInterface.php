<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\ActionType\Validation;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;

interface ActionValidatorInterface
{
    /**
     * Validate Action Metadata.
     *
     * @param MetadataInterface $metadata
     * @return bool
     */
    public function isValid(MetadataInterface $metadata): bool;
}
