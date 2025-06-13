<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler;

use Amasty\AdminActionsLog\Api\Logging\EntitySaveHandlerInterface;
use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Util\Ignore\ArrayFilter;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;

class Common implements EntitySaveHandlerInterface
{
    /**
     * @var ArrayFilter\ScalarValueFilter
     */
    private $scalarValueFilter;

    /**
     * @var ArrayFilter\KeyFilter
     */
    private $keyFilter;

    /**
     * @var array
     */
    protected $dataKeysIgnoreList = [];

    public function __construct(
        ArrayFilter\ScalarValueFilter $scalarValueFilter,
        ArrayFilter\KeyFilter $keyFilter
    ) {
        $this->scalarValueFilter = $scalarValueFilter;
        $this->keyFilter = $keyFilter;
    }

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\Framework\Model\AbstractModel $object */
        $object = $metadata->getObject();

        return [
            LogEntry::ELEMENT_ID => (int)$object->getId(),
        ];
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return array
     */
    public function processBeforeSave($object): array
    {
        if (!$object instanceof \Magento\Framework\Model\AbstractModel) {
            return [];
        }

        return $this->filterObjectData((array)$object->getOrigData());
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return array
     */
    public function processAfterSave($object): array
    {
        if (!$object instanceof \Magento\Framework\Model\AbstractModel) {
            return [];
        }

        return $this->filterObjectData((array)$object->getData());
    }

    protected function filterObjectData(array $data): array
    {
        $data = $this->scalarValueFilter->filter($data);
        $data = $this->keyFilter->filter($data, $this->dataKeysIgnoreList ?? []);

        return $data;
    }
}
