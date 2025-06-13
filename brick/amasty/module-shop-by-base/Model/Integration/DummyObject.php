<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\Integration;

use Magento\Framework\Exception\LocalizedException;

class DummyObject
{
    /**
     * @param string $method
     * @param array $args
     * @return null
     * @throws LocalizedException
     */
    public function __call($method, $args)
    {
        if (substr($method, 0, 3) === 'get') {
            return null;
        }

        throw new LocalizedException(
            __(
                'Requested Improved Navigation submodule is disabled. Only read methods is allowed.'
            )
        );
    }
}
