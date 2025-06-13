<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Source;

abstract class AbstractFilterDataPosition implements \Magento\Framework\Option\ArrayInterface
{
    public const AFTER = 'after';
    public const BEFORE = 'before';
    public const REPLACE = 'replace';
    public const DO_NOT_ADD = 'do-not-add';

    /**
     * @var string
     */
    private $label;

    /**
     * @return mixed
     */
    abstract public function initLabel();

    public function __construct()
    {
        $this->initLabel();
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::BEFORE,
                'label' => __('Before %1', $this->label)
            ],
            [
                'value' => self::AFTER,
                'label' => __('After %1', $this->label)
            ],
            [
                'value' => self::REPLACE,
                'label' => __('Replace %1', $this->label)
            ],
            [
                'value' => self::DO_NOT_ADD,
                'label' => __('Do Not Add')
            ]
        ];
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }
}
