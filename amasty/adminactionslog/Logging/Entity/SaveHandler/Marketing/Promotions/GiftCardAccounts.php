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

class GiftCardAccounts extends Common
{
    public const CATEGORY = 'admin/giftcardaccount/edit';

    /**
     * @var string[]
     */
    protected $dataKeysIgnoreList = [
        '_first_store_id',
        'form_key',
    ];

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\GiftCardAccount\Model\Giftcardaccount $giftCardAccount */
        $giftCardAccount = $metadata->getObject();

        return [
            LogEntry::ITEM =>  __('Gift Card Account #%1', $giftCardAccount->getId()),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('Gift Card Account'),
            LogEntry::ELEMENT_ID => (int)$giftCardAccount->getId(),
            LogEntry::STORE_ID => (int)$giftCardAccount->getStoreId()
        ];
    }
}
