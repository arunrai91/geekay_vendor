<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Customer;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;

class CustomerSegment extends Common
{
    public const CATEGORY = 'customersegment/index/edit';

    /**
     * @var string[]
     */
    protected $dataKeysIgnoreList = [
        'id',
        'segment_id',
        'form_key',
    ];

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\CustomerSegment\Model\Segment $segment */
        $segment = $metadata->getObject();

        return [
            LogEntry::ITEM => $segment->getName(),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('Customer Segment'),
            LogEntry::ELEMENT_ID => (int)$segment->getId(),
        ];
    }
}
