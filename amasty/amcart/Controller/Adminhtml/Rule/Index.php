<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Controller\Adminhtml\Rule;

use Amasty\Acart\Controller\Adminhtml\Rule;

class Index extends Rule
{
    public function execute()
    {
        $resultPage = $this->initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Campaigns'));

        return $resultPage;
    }
}
