<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Cms;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;
use Magento\Store\Model\Store;

class Page extends Common
{
    public const CATEGORY = 'admin/cms_page/edit/';

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\Cms\Model\Page $page */
        $page = $metadata->getObject();

        return [
            LogEntry::ITEM => $page->getTitle(),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('CMS Page'),
            LogEntry::ELEMENT_ID => (int)$page->getId(),
            LogEntry::STORE_ID => Store::DEFAULT_STORE_ID,
            LogEntry::PARAMETER_NAME => 'page_id'
        ];
    }
}
