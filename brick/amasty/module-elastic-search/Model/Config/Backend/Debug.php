<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Config\Backend;

class Debug extends \Magento\Framework\App\Config\Value
{
    public const LIFE_TIME = 300;

    /**
     * @return $this|\Magento\Framework\App\Config\Value
     */
    public function afterLoad()
    {
        if ($this->getValue()) {
            $this->setValue($this->isActive($this->getValue()));
        }
        return $this;
    }

    /**
     * @param float $value
     * @return bool
     */
    public function isActive($value)
    {
        return (microtime(true) - $value) < self::LIFE_TIME;
    }

    /**
     * @return $this|\Magento\Framework\App\Config\Value
     */
    public function beforeSave()
    {
        if ($this->getValue()) {
            $this->setValue(microtime(true));
        }
        return $this;
    }
}
