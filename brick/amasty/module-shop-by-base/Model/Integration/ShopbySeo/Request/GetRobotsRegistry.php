<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\Integration\ShopbySeo\Request;

use Amasty\ShopbySeo\Model\Request\RobotsRegistry;

class GetRobotsRegistry
{
    /**
     * @var array
     */
    private array $data;

    public function __construct(
        array $data = []
    ) {
        $this->data = $data;
    }

    /**
     * @return RobotsRegistry|null
     */
    public function execute(): ?RobotsRegistry
    {
        return $this->data['object'] ?? null;
    }
}
