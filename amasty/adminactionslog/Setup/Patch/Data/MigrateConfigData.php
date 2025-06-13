<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Setup\Patch\Data;

use Amasty\AdminActionsLog\Model\ConfigProvider;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class MigrateConfigData implements DataPatchInterface
{
    /**
     * @var array
     */
    private $changedConfigFields = [
        'log/log_enable_visit_history' => ConfigProvider::LOG_ENABLE_VISIT_HISTORY,
        'log/log_all_admins' => ConfigProvider::LOG_ALL_ADMINS,
        'log/log_admin_users' => ConfigProvider::LOG_ADMIN_USERS,
        'log/log_delete_logs_after_days' => ConfigProvider::ACTIONS_LOG_PERIOD,
        'log/log_delete_login_attempts_after_days' => ConfigProvider::LOGIN_ATTEMPTS_LOG_PERIOD,
        'log/log_delete_pages_history_after_days' => ConfigProvider::VISIT_HISTORY_LOG_PERIOD,
        'geolocation/geolocation_enable' => ConfigProvider::GEOLOCATION_ENABLE,
        'restore/restore_popup_message' => ConfigProvider::RESTORE_POPUP_MESSAGE,
        'successful_log_mailing/enabled' => ConfigProvider::SUCCESSFUL_LOG_MAILING_ENABLED,
        'successful_log_mailing/template' => ConfigProvider::SUCCESSFUL_LOG_MAILING_TEMPLATE,
        'successful_log_mailing/send_to_mail' => ConfigProvider::SUCCESSFUL_LOG_MAILING_SEND_TO,
        'unsuccessful_log_mailing/enabled' => ConfigProvider::UNSUCCESSFUL_LOG_MAILING_ENABLED,
        'unsuccessful_log_mailing/template' => ConfigProvider::UNSUCCESSFUL_LOG_MAILING_TEMPLATE,
        'unsuccessful_log_mailing/send_to_mail' => ConfigProvider::UNSUCCESSFUL_LOG_MAILING_SEND_TO,
        'suspicious_log_mailing/enabled' => ConfigProvider::SUSPICIOUS_LOG_MAILING_ENABLED,
        'suspicious_log_mailing/template' => ConfigProvider::SUSPICIOUS_LOG_MAILING_TEMPLATE,
        'suspicious_log_mailing/send_to_mail' => ConfigProvider::SUSPICIOUS_LOG_MAILING_SEND_TO
    ];

    /**
     * @var State
     */
    private $appState;

    /**
     * @var ConfigInterface
     */
    private $resourceConfig;

    public function __construct(
        State $appState,
        ConfigInterface $resourceConfig
    ) {
        $this->appState = $appState;
        $this->resourceConfig = $resourceConfig;
    }

    public function apply(): MigrateConfigData
    {
        $this->appState->emulateAreaCode(Area::AREA_ADMINHTML, [$this, 'updateConfig']);

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

    /**
     * Update config patches
     */
    public function updateConfig(): void
    {
        foreach ($this->changedConfigFields as $oldPath => $newPath) {
            $oldPathData = $this->getConfigValues($oldPath);

            if (!$oldPathData) {
                continue;
            }
            foreach ($oldPathData as $record) {
                $this->changeConfigData($oldPath, $record);

                $this->resourceConfig->saveConfig(
                    'amaudit/' . $newPath,
                    $record['value'],
                    $record['scope'],
                    $record['scope_id']
                );
                $this->resourceConfig->deleteConfig(
                    $record['path'],
                    $record['scope'],
                    $record['scope_id']
                );
            }
        }
    }

    /**
     * @param string $path
     *
     * @return array
     * @throws LocalizedException
     */
    private function getConfigValues(string $path): array
    {
        $connection = $this->resourceConfig->getConnection();
        $select = $connection->select()->from(
            $this->resourceConfig->getMainTable()
        )->where(
            'path = ?',
            'amaudit/' . $path
        );

        return $connection->fetchAll($select);
    }

    private function changeConfigData($oldPath, &$record): void
    {
        switch ($oldPath) {
            case 'successful_log_mailing/template':
            case 'unsuccessful_log_mailing/template':
            case 'suspicious_log_mailing/template':
                if ($record['value'] == 'amaudit_successful_log_mailing_template') {
                    $record['value'] = 'amaudit_email_notifications_successful_log_mailing_template';
                }
                if ($record['value'] == 'amaudit_unsuccessful_log_mailing_template') {
                    $record['value'] = 'amaudit_email_notifications_unsuccessful_log_mailing_template';
                }
                if ($record['value'] == 'amaudit_suspicious_log_mailing_template') {
                    $record['value'] = 'amaudit_email_notifications_suspicious_log_mailing_template';
                }
                break;
        }
    }
}
