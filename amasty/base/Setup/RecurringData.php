<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Setup;

use Amasty\Base\Model\SysInfo\Command\LicenceService\RegisterLicenceKey;
use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\Module\Manager;
use Magento\Framework\Module\Status;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Psr\Log\LoggerInterface;

/**
 * @codeCoverageIgnore
 */
class RecurringData implements InstallDataInterface
{
    public const NOTIFICATION_TABLE = 'adminnotification_inbox';
    public const IS_AMASTY_COLUMN = 'is_amasty';
    public const EXPIRATION_COLUMN = 'expiration_date';
    public const IMAGE_URL_COLUMN = 'image_url';

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var Status
     */
    private $moduleStatus;

    /**
     * @var array
     */
    private $modulesToDisable = [];

    /**
     * @var RegisterLicenceKey|null
     */
    private $registerLicenceKeyCommand;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Manager $moduleManager,
        Status $moduleStatus,
        array $modulesToDisable = [],
        RegisterLicenceKey $registerLicenceKeyCommand = null,
        State $appState = null,
        LoggerInterface $logger = null
    ) {
        $this->moduleManager = $moduleManager;
        $this->moduleStatus = $moduleStatus;
        $this->modulesToDisable = $this->initModulesToDisable($modulesToDisable);
        $this->registerLicenceKeyCommand = $registerLicenceKeyCommand
            ?? ObjectManager::getInstance()->get(RegisterLicenceKey::class);
        $this->appState = $appState
            ?? ObjectManager::getInstance()->get(State::class);
        $this->logger = $logger
            ?? ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (!empty($this->modulesToDisable)) {
            $this->moduleStatus->setIsEnabled(false, $this->modulesToDisable);
        }
        $setup->startSetup();
        $this->processNotificationTable($setup);
        $this->processInstanceRegistration();
        $setup->endSetup();
    }

    /**
     * Will try to register instance in case of domain change
     */
    private function processInstanceRegistration(): void
    {
        try {
            $this->appState->emulateAreaCode(
                Area::AREA_CRONTAB,
                [$this->registerLicenceKeyCommand, 'execute']
            );
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * Added through recurring because db_schema won't uninstall
     * our columns when AdminNotification module is disabled.
     * Consider refactoring to store amasty columns data in separate table.
     */
    private function processNotificationTable(ModuleDataSetupInterface $setup): void
    {
        if ($setup->getConnection()->isTableExists($setup->getTable(self::NOTIFICATION_TABLE))) {
            if (!$this->notificationTableColumnExist($setup, self::IS_AMASTY_COLUMN)) {
                $this->addIsAmastyField($setup);
            }

            if (!$this->notificationTableColumnExist($setup, self::EXPIRATION_COLUMN)) {
                $this->addExpireField($setup);
            }

            if (!$this->notificationTableColumnExist($setup, self::IMAGE_URL_COLUMN)) {
                $this->addImageUrlField($setup);
            }
        }
    }

    private function initModulesToDisable(array $modulesToDisable): array
    {
        $result = [];

        foreach (array_unique($modulesToDisable) as $module) {
            if ($this->moduleManager->isEnabled($module)) {
                $result[] = $module;
            }
        }

        return $result;
    }

    private function notificationTableColumnExist(ModuleDataSetupInterface $setup, string $column): bool
    {
        return (bool)$setup->getConnection()->tableColumnExists(
            $setup->getTable(self::NOTIFICATION_TABLE),
            $column
        );
    }

    private function addIsAmastyField(ModuleDataSetupInterface $setup): void
    {
        $setup->getConnection()->addColumn(
            $setup->getTable(self::NOTIFICATION_TABLE),
            self::IS_AMASTY_COLUMN,
            [
                'type' => Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Is Amasty Notification'
            ]
        );
    }

    private function addExpireField(ModuleDataSetupInterface $setup): void
    {
        $setup->getConnection()->addColumn(
            $setup->getTable(self::NOTIFICATION_TABLE),
            self::EXPIRATION_COLUMN,
            [
                'type' => Table::TYPE_DATETIME,
                'nullable' => true,
                'default' => null,
                'comment' => 'Expiration Date'
            ]
        );
    }

    private function addImageUrlField(ModuleDataSetupInterface $setup): void
    {
        $setup->getConnection()->addColumn(
            $setup->getTable(self::NOTIFICATION_TABLE),
            self::IMAGE_URL_COLUMN,
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => null,
                'comment' => 'Image Url'
            ]
        );
    }
}
