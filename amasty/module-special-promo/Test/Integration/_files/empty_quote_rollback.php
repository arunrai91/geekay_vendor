<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

use Magento\Quote\Model\ResourceModel\Quote\Collection;
use Magento\TestFramework\Helper\Bootstrap;

$quoteCollection = Bootstrap::getObjectManager()->create(Collection::class);
foreach ($quoteCollection as $quote) {
    $quote->delete();
}
