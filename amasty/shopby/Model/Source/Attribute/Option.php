<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Source\Attribute;

use Magento\Framework\Option\ArrayInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Swatches\Helper\Media as SwatchHelper;
use Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory as SwatchCollectionFactory;

class Option implements ArrayInterface
{
    public const SWATCH = 1;

    public const SWATCH_IMAGE = 2;

    /**
     * @var EavConfig
     */
    private EavConfig $eavConfig;

    /**
     * @var array
     */
    private $options;

    /**
     * @var int
     */
    private $skipAttributeId;

    /**
     * @var SwatchHelper
     */
    private SwatchHelper $swatchHelper;

    /**
     * @var null
     */
    private $swatchesByOptionId = null;

    /**
     * @var SwatchCollectionFactory
     */
    private SwatchCollectionFactory $swatchCollectionFactory;

    public function __construct(
        EavConfig $eavConfig,
        SwatchHelper $swatchHelper,
        SwatchCollectionFactory $swatchCollectionFactory
    ) {
        $this->eavConfig = $eavConfig;
        $this->swatchHelper = $swatchHelper;
        $this->swatchCollectionFactory = $swatchCollectionFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = [];

            $collection = $this->getCollection();
            foreach ($collection as $attribute) {
                $value = [
                    'label' => $attribute->getFrontendLabel()
                ];
                $options = [];

                foreach ($attribute->getOptions() as $option) {
                    $options[] = [
                        'value' => $option->getValue(),
                        'label' => $option->getLabel()
                    ];
                }
                $value['value'] = $options;
                $this->options[] = $value;
            }
        }
        return $this->options;
    }

    /**
     * @return array
     */
    public function toExtendedArray()
    {
        $data = [];
        $collection = $this->getCollection(0);
        foreach ($collection as $attribute) {
            $options = [];
            try {
                foreach ($attribute->getOptions() as $option) {
                    $scope = [
                        'value' => $option->getValue(),
                        'label' => $option->getLabel()
                    ];
                    // @codingStandardsIgnoreLine
                    $options[] = array_merge(
                        $scope,
                        $this->getSwatches($option->getValue())
                    );
                }

                $data[$attribute->getAttributeId()] = ['options' => $options, 'type' => $attribute->getFrontendInput()];
            } catch (\Exception $e) {
                continue;
            }
        }

        return $data;
    }

    /**
     * @param $optionId
     * @return mixed
     */
    public function getSwatches($optionId)
    {
        $data = ['type' => 0, 'swatch' => '', 'id' => $optionId];
        if ($item = $this->getSwatchByOptionId($optionId)) {
            $data['type'] = $item->getType();
            if ($item->getType() == self::SWATCH_IMAGE) {
                $data['swatch'] = $this->swatchHelper->getSwatchAttributeImage('swatch_image', $item->getValue());
            } else {
                $data['swatch'] = $item->getValue();
            }
        }

        return $data;
    }

    /**
     * @param int $optionId
     * @return mixed|null
     */
    private function getSwatchByOptionId($optionId)
    {
        if ($this->swatchesByOptionId === null) {
            $this->swatchesByOptionId = [];
            $collection = $this->swatchCollectionFactory->create();
            $collection->addFieldToFilter('store_id', 0);
            foreach ($collection as $item) {
                $this->swatchesByOptionId[$item->getOptionId()] = $item;
            }
        }

        return isset($this->swatchesByOptionId[$optionId]) ? $this->swatchesByOptionId[$optionId] : null;
    }

    /**
     * @param int $boolean
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCollection($boolean = 1)
    {
        $collection = $this->eavConfig->getEntityType(
            \Magento\Catalog\Model\Product::ENTITY
        )->getAttributeCollection();

        $collection->join(
            ['catalog_eav' => $collection->getTable('catalog_eav_attribute')],
            'catalog_eav.attribute_id=main_table.attribute_id',
            []
        )->addFieldToFilter('catalog_eav.is_filterable', 1);

        if ($this->skipAttributeId !== null) {
            $collection->addFieldToFilter('main_table.attribute_id', ['neq' => $this->skipAttributeId]);
        }
        if (!$boolean) {
            $collection->addFieldToFilter('main_table.frontend_input', ['neq' => 'boolean']);
        }
        return $collection;
    }

    /**
     * @param $skipAttributeId
     * @return $this
     */
    public function skipAttributeId($skipAttributeId)
    {
        $this->skipAttributeId = $skipAttributeId;
        return $this;
    }
}
