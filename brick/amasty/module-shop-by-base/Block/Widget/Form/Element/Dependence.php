<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Block\Widget\Form\Element;

use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

class Dependence extends \Magento\Backend\Block\Widget\Form\Element\Dependence
{
    /**
     * @var JsonSerializer
     */
    private JsonSerializer $jsonSerializer;

    /**
     * @var \Amasty\ShopbyBase\Model\Source\DisplayMode
     */
    private $displayModeSource;

    /**
     * @var array
     */
    private $groupValues = [];

    /**
     * @var array
     */
    private $fieldsets = [];

    /**
     * @var array
     */
    private $groupFields = [];

    public function __construct(
        JsonSerializer $jsonSerializer,
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory $fieldFactory,
        \Amasty\ShopbyBase\Model\Source\DisplayMode $displayModeSource,
        array $data = []
    ) {
        parent::__construct($context, $jsonEncoder, $fieldFactory, $data);
        $this->displayModeSource = $displayModeSource;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_depends) {
            return '';
        }

        $this->addConfigOptions($this->getPreparedOptions());

        return '<script>
            require(["Amasty_ShopbyBase/js/display-mode"], function() {
                var controller = new AmastyFormElementDependenceController(' . $this->getConfig() . ');
            });</script>';
    }

    /**
     * @return array|string
     */
    private function getConfig()
    {
        $config = [];
        $configItems = [$this->getGroupValues(), $this->getGroupFields(), $this->getFieldSets()];
        foreach ($configItems as $item) {
            $config[] = $item ? $this->jsonSerializer->serialize($item) : 'null';
        }

        $config = implode(', ', $config);
        $config = $this->_getDependsJson() . ', ' . $config .
            ($this->_configOptions ? ', ' . $this->jsonSerializer->serialize($this->_configOptions) : '');

        return $config;
    }

    /**
     * @return array
     */
    public function getPreparedOptions()
    {
        return [
            "levels_up" => 1,
            "notices" => $this->displayModeSource->getNotices(),
            "enabled_types" => $this->displayModeSource->getEnabledTypes(),
            "change_labels" => $this->displayModeSource->getChangeLabels()
        ];
    }

    /**
     * @param $fieldName
     * @param $fieldNameFrom
     * @param $dependencies
     * @param $values
     */
    public function addGroupValues($fieldName, $fieldNameFrom, $dependencies, $values)
    {
        $this->groupValues[$fieldName][$fieldNameFrom] = [
            'dependencies' => $dependencies,
            'values' => $values
        ];
    }

    /**
     * @param $fieldSetName
     * @param $fieldNameFrom
     * @param $values
     */
    public function addFieldsets($fieldSetName, $fieldNameFrom, $values)
    {
        $this->fieldsets[$fieldSetName][$fieldNameFrom] = $values;
    }

    /**
     * @param $fieldName
     * @param $group
     */
    public function addFieldToGroup($fieldName, $group)
    {
        $this->groupFields[$fieldName] = $group;
    }

    /**
     * @return array
     */
    public function getGroupValues()
    {
        return $this->groupValues;
    }

    /**
     * @return array
     */
    public function getFieldSets()
    {
        return $this->fieldsets;
    }

    /**
     * @return array
     */
    public function getGroupFields()
    {
        return $this->groupFields;
    }
}
