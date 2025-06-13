<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2021-01-19T19:26:03+00:00
 * File:          Helper/GracefulDie.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Helper;

use Xtento\OrderExport\Model\Log;

class GracefulDie
{
    protected static $isInitialized = false;
    protected static $isEnabled = false;

    public static function enable()
    {
        return; // Disabled, catched other error messages too. Only enabling for debugging.
        self::$isEnabled = true;
        if (!self::$isInitialized) {
            register_shutdown_function(['\Xtento\OrderExport\Helper\GracefulDie', 'beforeDieFromShutdown']); // Fatal error or similar
            self::$isInitialized = true;
        }
    }

    public static function disable()
    {
        self::$isEnabled = false;
    }

    /**
     * @param null $message
     * @param bool $exit
     */
    public static function beforeDie($message = null, $exit = false)
    {
        if (self::$isEnabled) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $registry = $objectManager->get('\Magento\Framework\Registry');
            $logEntry = $registry->registry('orderexport_log');
            if ($logEntry && $logEntry->getId()) {
                if (strstr($message, 'should always be of the type int since Symfony') !== false) {
                    return; // Ignore
                }
                $logEntry->setResult(Log::RESULT_FAILED);
                $logEntry->addResultMessage($message);
                $logEntry->setResultMessage($logEntry->getResultMessages());
                $logEntry->save();
                if (strlen($message) > 16) {
                    // No empty error message
                    $objectManager->get('\Xtento\OrderExport\Model\Export')->setLogEntry($logEntry)->errorEmailNotification();
                }
            }
        }
    }

    public static function beforeDieFromShutdown()
    {
        $lastError = error_get_last();
        if (isset($lastError['type']) && ($lastError['type'] === E_USER_DEPRECATED || $lastError['type'] == E_DEPRECATED)) {
            return; // No deprecated code in our code, but often libraries use deprecated code.
        }
        $message = 'Shutdown/Crash: ' . print_r($lastError, true);
        //'Stack Trace: ' . PHP_EOL . (new \Exception())->__toString();

        self::beforeDie($message, false);
    }
}