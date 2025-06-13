<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;

class EavAttributeSet extends Common
{
    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
        $attributeSet = $metadata->getObject();

        return [
            LogEntry::ITEM => $attributeSet->getAttributeSetName(),
            LogEntry::CATEGORY => 'catalog/product_set/edit',
            LogEntry::CATEGORY_NAME => __('Product Attribute Set'),
            LogEntry::ELEMENT_ID => (int)$attributeSet->getAttributeSetId(),
            LogEntry::PARAMETER_NAME => 'id'
        ];
    }
}
