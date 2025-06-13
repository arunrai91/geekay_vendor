<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Model\EmailTemplate\ProcessorPool;

use Magento\Framework\Mail\TemplateInterface;

interface LegacyTemplateProcessorInterface
{
    public function execute(TemplateInterface $template);
}
