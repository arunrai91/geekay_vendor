<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Marketing\Communications;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;

class NewsletterTemplates extends Common
{
    public const CATEGORY = 'newsletter/template/edit';

    /**
     * @var string[]
     */
    protected $dataKeysIgnoreList = [
        'template_id',
        'added_at'
    ];

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\Newsletter\Model\Template $newsletterTemplate */
        $newsletterTemplate = $metadata->getObject();

        return [
            LogEntry::ITEM => $newsletterTemplate->getTemplateCode(),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('Newsletter Template'),
            LogEntry::ELEMENT_ID => (int)$newsletterTemplate->getId(),
            LogEntry::STORE_ID => (int)$newsletterTemplate->getStoreId()
        ];
    }
}
