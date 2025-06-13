<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model;

use Amasty\Base\Debug\Log;

class Debug
{
    public const LOG_FILE_NAME = 'amasty_elastic_search.log';

    /**
     * @param $variable
     * @param bool $showBacktrace
     * @return $this
     */
    public function debug($variable, $showBacktrace = false)
    {
        //for local debugging set return true for Amasty/Base/Debug/VarDump.php : isAllowed()
        if (class_exists(Log::class)) {
            Log::setLogFile(self::LOG_FILE_NAME);
            Log::execute($variable);
            if ($showBacktrace) {
                Log::backtrace();
            }
        }

        return $this;
    }
}
