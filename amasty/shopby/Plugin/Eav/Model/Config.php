<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Eav\Model;

use Amasty\Shopby\Model\Search\RequestGenerator;
use Magento\Eav\Model\Config as ConfigModel;

class Config
{
    public function beforeGetAttribute(ConfigModel $subject, $entityType, $code)
    {
        if (is_string($code) &&
            ($pos = strpos($code, RequestGenerator::FAKE_SUFFIX)) !== false) {
            $code = substr($code, 0, $pos);
        }

        return [$entityType, $code];
    }
}
