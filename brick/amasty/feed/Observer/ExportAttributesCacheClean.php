<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Observer;

use Amasty\Feed\Api\Data\FeedInterface;
use Amasty\Feed\Model\Export\Product\Attributes\AttributesCache;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * amfeed_export_end
 */
class ExportAttributesCacheClean implements ObserverInterface
{
    /**
     * @var AttributesCache
     */
    private $attributesCache;

    public function __construct(
        AttributesCache $attributesCache
    ) {
        $this->attributesCache = $attributesCache;
    }

    public function execute(Observer $observer): void
    {
        /** @var FeedInterface $feed */
        if ($feed = $observer->getData('feed')) {
            $this->attributesCache->flush((int)$feed->getEntityId());
        }
    }
}
