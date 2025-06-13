<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Setup\Patch\Data;

use Amasty\ShopbyBase\Model\Cache\Type;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class EnableShopByCache implements DataPatchInterface
{
    /**
     * @var StateInterface
     */
    private $cacheState;

    public function __construct(
        StateInterface $cacheState
    ) {
        $this->cacheState = $cacheState;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): self
    {
        $this->cacheState->setEnabled(Type::TYPE_IDENTIFIER, true);
        $this->cacheState->persist();

        return $this;
    }
}
