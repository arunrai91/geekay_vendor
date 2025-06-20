<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Debug;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;

/**
 * For Remote Debug
 * Output is to front
 * @codeCoverageIgnore
 * @codingStandardsIgnoreFile
 */
class VarDump
{
    /**
     * @var array
     */
    private static $addressPath = [
        'REMOTE_ADDR',
        'HTTP_X_REAL_IP',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR'
    ];

    /**
     * @var array
     */
    private static $amastyIps = [
        '213.184.226.82',
        '87.252.238.217',
        '82.209.247.206',
    ];

    /**
     * @var int
     */
    private static $objectDepthLevel = 1;

    /**
     * @var int
     */
    private static $arrayDepthLevel = 2;

    /**
     * @param int $level
     */
    public static function setObjectDepthLevel($level)
    {
        self::$objectDepthLevel = (int)$level;
    }

    /**
     * @param int $level
     */
    public static function setArrayDepthLevel($level)
    {
        self::$arrayDepthLevel = (int)$level;
    }

    public static function execute()
    {
        if (self::isAllowed()) {
            foreach (func_get_args() as $var) {
                System\Beautifier::getInstance()->beautify(self::dump($var));
            }
        }
    }

    /**
     * @param array $array
     * @param int   $level
     *
     * @return array|string
     */
    private static function castArray($array, $level)
    {
        if ($level > self::$arrayDepthLevel) {
            return '...';
        }
        $result = [];
        foreach ($array as $key => $value) {
            switch (strtolower(gettype($value))) {
                case 'object':
                    $result[$key] = self::castObject($value, $level + 1);
                    break;
                case 'array':
                    $result[$key] = self::castArray($value, $level + 1);
                    break;
                default:
                    $result[$key] = $value;
                    break;
            }
        }
        return $result;
    }

    /**
     * @param     $object
     * @param int $level
     *
     * @return System\AmastyDump|string
     */
    private static function castObject($object, $level)
    {
        if ($level > self::$objectDepthLevel) {
            return '...';
        }
        $reflection = new \ReflectionClass($object);

        $amastyDump = new System\AmastyDump();

        $amastyDump->className = $reflection->getName();
        $amastyDump->shortClassName = $reflection->getShortName();

        foreach ($reflection->getMethods() as $method) {
            $amastyDump->methods[] = $method->getName();
        }
        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            try {
                $propertyValue = $property->getValue($object);
            } catch (\Throwable $e) {
                //should be replaced to ReflectionProperty::isInitialized check after end of php 7.3 support
                $propertyValue = null;
            }

            switch (strtolower(gettype($propertyValue))) {
                case 'object':
                    $amastyDump->properties[$property->name] = self::castObject($propertyValue, $level + 1);
                    break;
                case 'array':
                    $amastyDump->properties[$property->name] = self::castArray($propertyValue, $level + 1);
                    break;
                default:
                    $amastyDump->properties[$property->name] = $propertyValue;
                    break;
            }
        }
        return $amastyDump;
    }

    /**
     * @param mixed $var
     *
     * @return array|mixed
     */
    public static function dump($var)
    {
        if (self::isAllowed()) {
            switch (strtolower(gettype($var))) {
                case 'object':
                    return self::castObject($var, 0);
                case 'array':
                    return self::castArray($var, 0);
                case 'resource':
                    return stream_get_meta_data($var);
                default:
                    return $var;
            }
        }

        return null;
    }

    /**
     * Check for Amasty Ips
     *
     * @return bool
     */
    public static function isAllowed()
    {
        $request = ObjectManager::getInstance()->get(RequestInterface::class);

        if ($request) {
            foreach (self::$addressPath as $path) {
                if (!empty($request->getServer($path))
                    && in_array($request->getServer($path), self::$amastyIps, true)
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param int $code
     */
    public static function amastyExit($code = 0)
    {
        if (self::isAllowed()) {
            if (class_exists(\Laminas\Console\Response::class)) {
                (new \Laminas\Console\Response())->send();
            }
        }
    }

    /**
     * @param string $string
     */
    public static function amastyEcho($string)
    {
        if (self::isAllowed()) {
            printf('%s', $string);
        }
    }
}
