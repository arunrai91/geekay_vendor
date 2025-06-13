<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\ActionType\Validation;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Magento\Framework\DataObject;

class ActionValidator implements ActionValidatorInterface
{
    /**
     * @var DataObject
     */
    private $actionsList;

    public function __construct(DataObject $actionsList)
    {
        $this->actionsList = $actionsList;
    }

    public function isValid(MetadataInterface $metadata): bool
    {
        return !in_array($metadata->getRequest()->getFullActionName(), $this->actionsList->getList());
    }
}
