<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Controller\Adminhtml\Blacklist;

use Amasty\Acart\Controller\Adminhtml\Blacklist;
use Magento\Framework\Controller\ResultFactory;

class NewAction extends Blacklist
{
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);

        return $result->forward('edit');
    }
}
