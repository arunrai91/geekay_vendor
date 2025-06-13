<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Setup;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\AggregatedFieldDataConverter;
use Magento\Framework\DB\FieldToConvert;
use Magento\Framework\ObjectManagerInterface;

class SerializedFieldDataConverter
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ResourceConnection
     */
    private $connectionResource;

    public function __construct(
        ObjectManagerInterface $objectManager,
        ResourceConnection $connectionResource
    ) {
        $this->objectManager = $objectManager;
        $this->connectionResource = $connectionResource;
    }

    /**
     * Convert metadata from serialized to JSON format:
     *
     * @param string|string[] $tableName
     * @param string $identifierField
     * @param string|string[] $fields
     * @return void
     */
    public function convertSerializedDataToJson($tableName, $identifierField, $fields)
    {
        /** @var AggregatedFieldDataConverter $fieldConverter */
        $fieldConverter = $this->objectManager->get(AggregatedFieldDataConverter::class);
        $convertData = [];

        if (is_array($fields)) {
            foreach ($fields as $field) {
                $convertData[] = $this->getConvertedData($tableName, $identifierField, $field);
            }
        } else {
            $convertData[] = $this->getConvertedData($tableName, $identifierField, $fields);
        }

        $fieldConverter->convert(
            $convertData,
            $this->connectionResource->getConnection()
        );
    }

    /**
     * @param string|string[] $tableName
     * @param string $identifierField
     * @param string $field
     * @return FieldToConvert
     */
    protected function getConvertedData($tableName, $identifierField, $field)
    {
        return new FieldToConvert(
            \Magento\Framework\DB\DataConverter\SerializedToJson::class,
            $this->connectionResource->getTableName($tableName),
            $identifierField,
            $field
        );
    }
}
