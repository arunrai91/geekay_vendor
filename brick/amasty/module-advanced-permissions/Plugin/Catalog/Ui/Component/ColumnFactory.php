<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Plugin\Catalog\Ui\Component;

class ColumnFactory
{
    /**
     * @var \Amasty\Rolepermissions\Model\Entity\Attribute\Source\Admins
     */
    private $admins;

    /**
     * @param \Amasty\Rolepermissions\Model\Entity\Attribute\Source\Admins $admins
     */
    public function __construct(
        \Amasty\Rolepermissions\Model\Entity\Attribute\Source\Admins $admins
    ) {
        $this->admins = $admins;
    }

    /**
     * @param \Magento\Catalog\Ui\Component\ColumnFactory         $columnFactory
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
     * @param                                                     $context
     * @param array                                               $config
     *
     * @return array
     */
    public function beforeCreate(
        \Magento\Catalog\Ui\Component\ColumnFactory $columnFactory,
        $attribute,
        $context,
        array $config = []
    ) {
        if ($attribute->getAttributeCode() === 'amrolepermissions_owner') {
            $attribute->setFrontendInput('select');
            $config['options'] = $this->admins->toOptionArray();
        }

        return [$attribute, $context, $config];
    }
}
