<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Setup\Patch\Data;

use Amasty\Acart\Model\RuleQuotesProcessor;
use Magento\Framework\FlagManager;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class AddInstallUpdateTime implements DataPatchInterface
{
    public const OLD_FLAG_KEY = 'amasty_acart_last_executed';

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        FlagManager $flagManager,
        DateTime $dateTime
    ) {
        $this->flagManager = $flagManager;
        $this->dateTime = $dateTime;
    }

    public function apply()
    {
        $oldFlag = $this->flagManager->getFlagData(self::OLD_FLAG_KEY);
        $executionTime = $oldFlag ?: $this->dateTime->gmtTimestamp();

        $this->flagManager->saveFlag(RuleQuotesProcessor::UPDATE_INSTALLATION_FLAG, $executionTime);
        $this->flagManager->deleteFlag(self::OLD_FLAG_KEY);

        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
