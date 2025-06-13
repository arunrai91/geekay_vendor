<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Api\Data;

interface ValidProductsSnapshotInterface
{
    public const ID = 'entity_id';
    public const FEED_ID = 'feed_id';
    public const VALID_PRODUCT_ID = 'valid_product_id';

    /**
     * @return int
     */
    public function getSnapId(): int;

    /**
     * @param int $id
     * @return void
     */
    public function setSnapId(int $id): void;

    /**
     * @return int
     */
    public function getFeedId(): int;

    /**
     * @param int $feedId
     * @return void
     */
    public function setFeedId(int $feedId): void;

    /**
     * @return int
     */
    public function getValidProductId(): int;

    /**
     * @param string $validProducts
     * @return void
     */
    public function setValidProductId(string $validProducts): void;
}
