<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Model\SectionsData;

use Magento\Checkout\Model\Session;

interface FlushSectionsInterface
{
    public function execute(Session $checkoutSession, array $sectionNames): void;
}
