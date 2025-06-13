<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Block\Adminhtml\System\Config\InformationBlocks\Validation;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\Manager;

class Mage245Fix implements MessageValidatorInterface
{
    public const MAGE245FIX_MODULE = 'Amasty_Mage245Fix';

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var bool
     */
    private $enableInversionCondition;

    public function __construct(
        Manager $moduleManager,
        ProductMetadataInterface $productMetadata,
        bool $enableInversionCondition = false
    ) {
        $this->moduleManager = $moduleManager;
        $this->productMetadata = $productMetadata;
        $this->enableInversionCondition = $enableInversionCondition;
    }

    public function isValid(): bool
    {
        return !$this->isMage245FixEnabled() && $this->isMagento245Version();
    }

    private function isMage245FixEnabled(): bool
    {
        return $this->enableInversionCondition
            ? !$this->moduleManager->isEnabled(self::MAGE245FIX_MODULE)
            : $this->moduleManager->isEnabled(self::MAGE245FIX_MODULE);
    }

    private function isMagento245Version(): bool
    {
        return $this->enableInversionCondition
            ? strpos($this->getMagentoVersion(), '2.4.5') === false
            : strpos($this->getMagentoVersion(), '2.4.5') !== false;
    }
    private function getMagentoVersion(): string
    {
        return $this->productMetadata->getVersion();
    }
}
