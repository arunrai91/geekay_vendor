<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Plugin\ClassyLlama\Plugin\Model\ConfigPlugin;

use ClassyLlama\AvaTax\Plugin\Model\ConfigPlugin;
use Magento\Config\Model\Config;

class DisableAmastySectionCheck
{
    /**
     * If section doesn't have data (for example because of config_path usage)
     * AvaTax plugin will throw fatal error
     * So we must disable it processing for our modules
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundAroundSave(
        ConfigPlugin $subject, // @phpstan-ignore class.notFound
        callable $proceed,
        Config $config,
        callable $origProceed
    ) {
        $section = $config->getSection();
        if (stripos($section, 'amasty') !== false || stripos($section, 'am') === 0) {
            return $origProceed();
        }

        return $proceed($config, $origProceed);
    }
}
