<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Api\Logging;

interface EntitySaveHandlerInterface
{
    /**
     * Method returns specific LogEntry's data after object saving/deleting.
     * @see \Amasty\AdminActionsLog\Logging\ActionType\AfterSave\Entity::prepareLogEntryData
     *
     * @param MetadataInterface $metadata
     * @return array
     */
    public function getLogMetadata(MetadataInterface $metadata): array;

    /**
     * Method for extracting and generating key => value array with data from concrete object
     * for LogDetail entity before saving main entity.
     * @see \Amasty\AdminActionsLog\Logging\ActionType\BeforeSave\Entity::execute
     *
     * @param $object
     * @return array
     */
    public function processBeforeSave($object): array;

    /**
     * Method for extracting and generating key => value array with data from concrete object
     * for LogDetail entity after saving main entity.
     * @see \Amasty\AdminActionsLog\Logging\ActionType\AfterSave\Entity::execute
     *
     * @param $object
     * @return array
     */
    public function processAfterSave($object): array;
}
