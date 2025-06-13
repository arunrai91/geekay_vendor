<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\Cache;

class Type extends \Magento\Framework\Cache\Frontend\Decorator\TagScope
{
    /**
     * Cache type code unique among all cache types
     */
    public const TYPE_IDENTIFIER = 'amasty_shopby';

    /**
     * Cache tag used to distinguish the cache type from all other cache
     */
    public const CACHE_TAG = 'AMASTY_SHOPBY';

    /**
     * @param \Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool
     */
    public function __construct(\Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool)
    {
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
    }
}
