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

class CatalogPriceRule extends Common
{
    public const CATEGORY = 'catalog_rule/promo_catalog/edit';

    /**
     * @var string[]
     */
    protected $dataKeysIgnoreList = [
        '_first_store_id',
        'form_key',
    ];

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\CatalogRule\Model\Rule $catalogPriceRule */
        $catalogPriceRule = $metadata->getObject();

        return [
            LogEntry::ITEM => $catalogPriceRule->getName(),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('Catalog Price Rule'),
            LogEntry::ELEMENT_ID => (int)$catalogPriceRule->getId()
        ];
    }
}
