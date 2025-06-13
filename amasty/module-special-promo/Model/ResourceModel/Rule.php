<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Model\ResourceModel;

use Amasty\Rules\Model\DiscountTypes;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\SalesRule\Api\Data\RuleInterface;

/**
 * Resource model for Rule object.
 */
class Rule extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public const TABLE_NAME = 'amasty_amrules_rule';

    /**
     * @var MetadataPool
     */
    private $metadata;

    public function __construct(
        Context $context,
        MetadataPool $metadata,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->metadata = $metadata;
    }

    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'entity_id');
    }

    public function disableSpecialPromoRules(): void
    {
        $mainTable = $this->getMainTable();
        $salesRuleTable = $this->getTable('salesrule');
        $linkField = $this->metadata->getMetadata(RuleInterface::class)->getLinkField();
        $select = $this->getConnection()->select()
            ->from($mainTable, ['salesrule_id'])
            ->join(
                [$salesRuleTable],
                $mainTable . '.salesrule_id = ' . $salesRuleTable . '.' . $linkField,
                ['is_active']
            )
            ->where('is_active = ?', 1)
            ->where('simple_action IN (?)', DiscountTypes::AMASTY_RULES_ACTIONS);

        $ruleIds = $this->getConnection()->fetchCol($select);
        if (!empty($ruleIds)) {
            $this->getConnection()->update($salesRuleTable, ['is_active' => 0], [$linkField . ' IN (?)' => $ruleIds]);
        }
    }
}
