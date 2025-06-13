<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Model\ResourceModel;

use Amasty\Acart\Model\EmailTemplate as EmailTemplateModel;
use Magento\Email\Model\ResourceModel\Template as ResourceTemplate;

class EmailTemplate extends ResourceTemplate
{
    public const TABLE_NAME = 'amasty_acart_email_template';

    /**
     * Initialize email template resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, EmailTemplateModel::TEMPLATE_ID);
    }
}
