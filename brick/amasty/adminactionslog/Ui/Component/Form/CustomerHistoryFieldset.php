<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Ui\Component\Form;

use Magento\Framework\View\Element\ComponentVisibilityInterface;

class CustomerHistoryFieldset extends \Magento\Ui\Component\Form\Fieldset implements ComponentVisibilityInterface
{
    public function isComponentVisible(): bool
    {
        return $this->context->getRequestParam('id') !== null;
    }
}
