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

class ProductGalleryEntry extends Common
{
    public function getLogMetadata(MetadataInterface $metadata): array
    {
        $image = $metadata->getObject();

        return [
            LogEntry::ITEM => $image->getFile(),
            LogEntry::CATEGORY => __('Product Gallery Image'),
            LogEntry::CATEGORY_NAME => __('Product Gallery Image'),
            LogEntry::ELEMENT_ID => (int)$image->getId()
        ];
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return array
     */
    public function processBeforeSave($object): array
    {
        if ($object->getId()) {
            return $this->filterObjectData($object->getData());
        } else {
            return $this->filterObjectData((array)$object->getOrigData());
        }
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return array
     */
    public function processAfterSave($object): array
    {
        return $this->filterObjectData($object->getData());
    }
}
