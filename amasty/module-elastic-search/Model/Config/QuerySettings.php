<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Config;

class QuerySettings extends \Magento\Framework\App\Config\Value
{
    /**
     * Keys for array configuration usage
     */
    public const ATTRIBUTE = 'attribute';
    public const WILDCARD = 'wildcard';
    public const SPELLING = 'spelling';
    public const COMBINING = 'combining';

    /**
     * Keys for DB storage
     */
    public const KEY_WILDCARD = 'w';
    public const KEY_SPELLING = 's';
    public const KEY_COMBINING = 'c';

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @var \Amasty\ElasticSearch\Model\Config\QuerySettingsProcessor
     */
    private $processor;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->serializer = $this->getData('serializer');
        $this->processor = $this->getData('processor');
    }

    /**
     * @inheritdoc
     */
    protected function _afterLoad()
    {
        $value = $this->getValue();
        $value = $this->processor->parseLoad($value);
        $value = $this->encodeArrayFieldValue($value);
        $this->setValue($value);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        $value = $this->makeStorableArrayFieldValue($value);
        if ($value) {
            $this->setValue($value);
        } else {
            $this->_dataSaveAllowed = false;
        }

        return $this;
    }

    /**
     * Make value ready for store
     *
     * @param string|array $value
     * @return string
     */
    private function makeStorableArrayFieldValue($value)
    {
        if (!is_array($value)) {
            $value = $this->unserializeValue($value);
        }
        $value = $this->decodeArrayFieldValue($value);
        $value = $this->serializeValue($value);
        return $value;
    }

    /**
     * Prepare configuration array of all searchable attributes for frontend
     *
     * @param array $value
     * @return array
     */
    private function encodeArrayFieldValue(array $value)
    {
        $result = [];
        $attributeCodes = $this->processor->getSearchableAttributeCodes();
        foreach ($value as $attributeCode => $data) {
            if (in_array($attributeCode, $attributeCodes, true)) {
                $result[$attributeCode] = [
                    self::ATTRIBUTE => $attributeCode,
                    self::WILDCARD => $data[self::WILDCARD],
                    self::SPELLING => $data[self::SPELLING],
                    self::COMBINING => $data[self::COMBINING]
                ];
            }
        }

        foreach ($attributeCodes as $code) {
            if (!isset($result[$code])) {
                $result[$code] = [
                    self::ATTRIBUTE => $code,
                    self::WILDCARD => 0,
                    self::SPELLING => 0,
                    self::COMBINING => 0
                ];
            }
        }

        return $result;
    }

    /**
     * Create a value from a storable representation
     *
     * @param int|float|string $value
     * @return array
     */
    private function unserializeValue($value)
    {
        $result = [];
        if (is_string($value) && !empty($value)) {
            $result = $this->serializer->unserialize($value);
        }

        return $result;
    }

    /**
     * Decode value from used in \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
     *
     * @param array $value
     * @return array
     */
    private function decodeArrayFieldValue(array $value)
    {
        unset($value['__empty']);
        foreach ($value as $attribute => $data) {
            if (!is_array($data) || !array_key_exists(self::COMBINING, $data)) {
                unset($value[$attribute]);
            }
        }

        return $value;
    }

    /**
     * Generate a storable representation of a value
     *
     * @param int|float|string|array $value
     * @return string
     */
    private function serializeValue($value): string
    {
        if (is_array($value) && !empty($value)) {
            $result = [];
            foreach ($value as $attributeCode => $data) {
                $wildcard = 0;
                if (isset($data[self::WILDCARD])) {
                    $wildcard = $this->prepareVariable($data[self::WILDCARD]);
                }

                $spelling = 0;
                if (isset($data[self::SPELLING])) {
                    $spelling = $this->prepareVariable($data[self::SPELLING]);
                }

                $combing = (int)($data[self::COMBINING] ?? 0);

                if ($wildcard || $spelling || $combing) {
                    $result[$attributeCode] = [
                        self::KEY_WILDCARD => $wildcard,
                        self::KEY_SPELLING => $spelling,
                        self::KEY_COMBINING => $combing
                    ];
                }
            }

            return $this->serializer->serialize($result);
        }

        return '';
    }

    /**
     * @param string $data
     */
    private function prepareVariable($data): int
    {
        if (strcasecmp($data, 'on') === 0) {
            return  1;
        }

        return (int) $data;
    }

    /**
     * @deprecated moved to separate class
     * @see \Amasty\ElasticSearch\Model\Config\QuerySettingsProcessor::getConfigValue
     *
     * @param string $attributeCode
     * @return array|null
     */
    public function getConfigValue($attributeCode)
    {
        return $this->processor->getConfigValue($attributeCode);
    }
}
