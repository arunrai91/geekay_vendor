<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Marketing\Promotions;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;

class CartPriceRule extends Common
{
    public const CATEGORY = 'sales_rule/promo_quote/edit';

    /**
     * @var string[]
     */
    protected $dataKeysIgnoreList = [
        '_first_store_id',
        'form_key',
    ];

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\SalesRule\Model\Rule $cartPriceRule */
        $cartPriceRule = $metadata->getObject();

        return [
            LogEntry::ITEM => $cartPriceRule->getName(),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('Cart Price Rule'),
            LogEntry::ELEMENT_ID => (int)$cartPriceRule->getId(),
        ];
    }

    public function processAfterSave($object): array
    {
        $object->load($object->getId());

        return parent::processAfterSave($object);
    }
}
