<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\ResourceModel;

use Magento\Catalog\Model\Product;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class IsProductAttributeExist extends AbstractDb
{
    public const ATTRIBUTE_TABLE = 'eav_attribute';

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var int|null
     */
    private $catalogProductEntityTypeId;

    public function __construct(
        EavConfig $eavConfig,
        Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->eavConfig = $eavConfig;
    }

    protected function _construct()
    {
        $this->_init(self::ATTRIBUTE_TABLE, AttributeInterface::ATTRIBUTE_CODE);
    }

    /**
     * @param string $attributeCode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(string $attributeCode): bool
    {
        $select = $this->getConnection()->select()->from(
            ['main_table' => $this->getMainTable()]
        )->where(
            sprintf('%s = ?', AttributeInterface::ATTRIBUTE_CODE),
            $attributeCode
        )->where(
            sprintf('%s = ?', AttributeInterface::ENTITY_TYPE_ID),
            $this->getCatalogProductEntityTypeId()
        );

        return (bool) $this->getConnection()->fetchOne($select);
    }

    private function getCatalogProductEntityTypeId(): int
    {
        if ($this->catalogProductEntityTypeId === null) {
            $this->catalogProductEntityTypeId = (int)$this->eavConfig->getEntityType(
                Product::ENTITY
            )->getEntityTypeId();
        }

        return $this->catalogProductEntityTypeId;
    }
}
