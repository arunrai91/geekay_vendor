<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Catalog;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;

class Category extends Common
{
    public const CATEGORY = 'catalog/category/edit';

    /**
     * @var string[]
     */
    protected $dataKeysIgnoreList = [
        'form_key',
        'entity_id',
        'updated_at',
        'url_key_create_redirect',
        'save_rewrites_history',
        'custom_design_from_is_formated'
    ];

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $metadata->getObject();

        if (!$category->getName()) {
            $category->load($category->getId()); // Force reload category in cases of mass delete, etc.
        }

        return [
            LogEntry::ITEM => $category->getName(),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('Catalog Category'),
            LogEntry::ELEMENT_ID => (int)$category->getId(),
            LogEntry::STORE_ID => (int)$category->getStoreId()
        ];
    }
}
