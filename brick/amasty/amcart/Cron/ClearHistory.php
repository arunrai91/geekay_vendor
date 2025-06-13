<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Cron;

class ClearHistory
{
    /**
     * @var \Amasty\Acart\Model\Cleaner
     */
    private $cleaner;

    public function __construct(
        \Amasty\Acart\Model\Cleaner $cleaner
    ) {
        $this->cleaner = $cleaner;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $this->cleaner->clearExpiredHistory()->clearExpiredRuleQuotes();

        return $this;
    }
}
