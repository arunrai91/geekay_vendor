<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Debug\System;

use Monolog\LogRecord;

class AmastyFormatter extends \Monolog\Formatter\LineFormatter
{
    /**
     * @param array|LogRecord $record
     *
     * @return string
     */
    public function format($record): string
    {
        if (!is_array($record)) {
            return parent::format($record);
        }

        $output = $this->format;
        $output = str_replace('%datetime%', date('H:i d/m/Y'), $output);
        $output = str_replace('%message%', $record['message'], $output);

        return $output;
    }
}
