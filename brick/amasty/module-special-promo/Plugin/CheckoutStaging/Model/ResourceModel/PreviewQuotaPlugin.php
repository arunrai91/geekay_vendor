<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Plugin\CheckoutStaging\Model\ResourceModel;

/**
 * Fix Magento issue with table prefix on preview
 */
class PreviewQuotaPlugin
{
    public const PREVIEW_QUOTA_TABLE = 'quote_preview';

    /**
     * @param \Magento\CheckoutStaging\Model\ResourceModel\PreviewQuota $subject
     * @param callable $proceed
     * @param int $id
     *
     * @return bool
     */
    public function aroundInsert(
        // @phpstan-ignore-next-line
        \Magento\CheckoutStaging\Model\ResourceModel\PreviewQuota $subject,
        callable $proceed,
        $id
    ) {
        $connection = $subject->getConnection();
        $select = $connection->select()
            ->from($subject->getTable(self::PREVIEW_QUOTA_TABLE)) // Amasty fix: added getTable call
            ->where('quote_id = ?', (int) $id);
        if (!empty($connection->fetchRow($select))) {
            return true;
        }
        return 1 === $connection->insert(
            $subject->getTable(self::PREVIEW_QUOTA_TABLE),
            ['quote_id' => (int) $id]
        );
    }
}
