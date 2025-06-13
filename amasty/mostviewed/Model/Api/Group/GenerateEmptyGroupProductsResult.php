<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Api\Group;

use Amasty\Mostviewed\Api\Data\GroupProductsResultInterface;
use Amasty\Mostviewed\Api\Data\GroupProductsResultInterfaceFactory;

class GenerateEmptyGroupProductsResult
{
    /**
     * @var GroupProductsResultInterfaceFactory
     */
    private $groupProductsResultFactory;

    public function __construct(GroupProductsResultInterfaceFactory $groupProductsResultFactory)
    {
        $this->groupProductsResultFactory = $groupProductsResultFactory;
    }

    public function execute(): GroupProductsResultInterface
    {
        return $this->groupProductsResultFactory->create();
    }
}
