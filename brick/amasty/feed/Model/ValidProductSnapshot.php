<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model;

use Amasty\Feed\Api\Data\ValidProductsSnapshotInterface;
use Amasty\Feed\Model\ValidProductSnapshot\ResourceModel\ValidProductSnapshot as ResourceModel;
use Magento\Framework\Model\AbstractModel;

class ValidProductSnapshot extends AbstractModel implements ValidProductsSnapshotInterface
{
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
        $this->setIdFieldName(self::ID);
    }

    public function getSnapId(): int
    {
        return (int)$this->getData(self::ID);
    }

    public function setSnapId(int $id): void
    {
        $this->setData(self::ID, $id);
    }

    public function getFeedId(): int
    {
        return (int)$this->getData(self::FEED_ID);
    }

    public function setFeedId(int $feedId): void
    {
        $this->setData(self::FEED_ID, $feedId);
    }

    public function getValidProductId(): int
    {
        return (int)$this->getData(self::VALID_PRODUCT_ID);
    }

    public function setValidProductId(string $validProducts): void
    {
        $this->setData(self::VALID_PRODUCT_ID, $validProducts);
    }
}
