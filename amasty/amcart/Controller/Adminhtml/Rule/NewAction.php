<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Controller\Adminhtml\Rule;

use Amasty\Acart\Controller\Adminhtml\Rule;
use Magento\Framework\Controller\ResultFactory;

class NewAction extends Rule
{
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);

        return $result->forward('edit');
    }
}
