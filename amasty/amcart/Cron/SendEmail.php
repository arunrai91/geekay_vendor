<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Cron;

use Amasty\Acart\Model\Mail\Processor;

class SendEmail
{
    /**
     * @var Processor
     */
    private $mailProcessor;

    public function __construct(
        Processor $mailProcessor
    ) {
        $this->mailProcessor = $mailProcessor;
    }

    public function execute(): void
    {
        $this->mailProcessor->process();
    }
}
