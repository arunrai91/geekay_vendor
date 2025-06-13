<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Model\System\Message\DisplayValidator;

use Amasty\Base\Model\MagentoVersion;
use Magento\Framework\Module\Manager;

class Mage248FixValidator implements DisplayValidatorInterface
{
    private const MAGENTO_VERSION = '2.4.8';
    private const FIX_MODULE = 'Amasty_Mage248Fix';

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var MagentoVersion
     */
    private $magentoVersion;

    public function __construct(
        Manager $moduleManager,
        MagentoVersion $magentoVersion
    ) {
        $this->moduleManager = $moduleManager;
        $this->magentoVersion = $magentoVersion;
    }

    public function needToShow(): bool
    {
        return $this->magentoVersion->get() === self::MAGENTO_VERSION
            && !$this->moduleManager->isEnabled(self::FIX_MODULE);
    }
}
