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

class Block extends Common
{
    public const CATEGORY = 'cms/block/edit';

    /**
     * @var string[]
     */
    protected $dataKeysIgnoreList = [
        '_first_store_id',
        'form_key',
    ];

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\Cms\Model\Block $block */
        $block = $metadata->getObject();

        return [
            LogEntry::ITEM => $block->getTitle(),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('CMS Block'),
            LogEntry::ELEMENT_ID => (int)$block->getId(),
            LogEntry::STORE_ID => Store::DEFAULT_STORE_ID,
            LogEntry::PARAMETER_NAME => 'block_id'
        ];
    }
}
