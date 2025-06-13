<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model;

use Amasty\Base\Model\ConfigProviderAbstract;

class Config extends ConfigProviderAbstract
{
    public const FEED_SECTION = 'amasty_feed/';

    public const BATCH_SIZE_FIELD = 'general/batch_size';
    public const FILE_PATH_FIELD = 'general/file_path';
    public const STORAGE_FOLDER = 'general/storage_folder';
    public const PREVIEW_ITEMS = 'general/preview_items';
    public const CATEGORY_PATH = 'general/category_path';

    public const ENABLED_FIELD = 'multi_process/enabled';
    public const PROCESS_COUNT_FIELD = 'multi_process/process_count';

    public const EVENTS_FIELD = 'notifications/events';
    public const SENDER_FIELD = 'notifications/email_sender';
    public const EMAILS_FIELD = 'notifications/emails';
    public const SUCCESS_TEMPLATE_FIELD = 'notifications/success_template';
    public const UNSUCCESS_TEMPLATE_FIELD = 'notifications/unsuccess_template';

    /**
     * @var string
     */
    protected $pathPrefix = self::FEED_SECTION;

    public function getCategoryPath(): int
    {
        return (int)$this->getValue(self::CATEGORY_PATH);
    }

    public function getItemsForPreview(): int
    {
        return (int)$this->getValue(self::PREVIEW_ITEMS);
    }

    public function getItemsPerPage(): int
    {
        return (int)$this->getValue(self::BATCH_SIZE_FIELD);
    }

    public function getSelectedEvents(): string
    {
        return (string)$this->getValue(self::EVENTS_FIELD);
    }

    public function getSuccessEmailTemplate(): string
    {
        return (string)$this->getValue(self::SUCCESS_TEMPLATE_FIELD);
    }

    public function getUnsuccessEmailTemplate(): string
    {
        return (string)$this->getValue(self::UNSUCCESS_TEMPLATE_FIELD);
    }

    public function getEmailSenderContact(): string
    {
        return (string)$this->getValue(self::SENDER_FIELD);
    }

    public function getEmails(): array
    {
        $result = [];
        if ($emails = (string)$this->getValue(self::EMAILS_FIELD)) {
            $result = array_map('trim', explode(',', $emails));
        }

        return $result;
    }

    public function getStorageFolder(): string
    {
        return (string)$this->getValue(self::STORAGE_FOLDER);
    }

    public function getFilePath(): string
    {
        return (string)$this->getValue(self::FILE_PATH_FIELD);
    }

    public function getMaxJobsCount(): int
    {
        if (!function_exists('pcntl_fork') || !$this->isSetFlag(self::ENABLED_FIELD)) {
            return 1;
        }
        $processCount = (int)$this->getValue(self::PROCESS_COUNT_FIELD);

        return max($processCount, 1);
    }
}
