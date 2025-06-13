<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Analytics\Collector;

class ViewCollector extends AbstractCollector implements CollectorInterface
{
    public const ACTION_TYPE = 'view';

    /**
     * Collect view analytics actions
     */
    public function execute(): void
    {
        $this->updateAnalytics(
            self::ACTION_TYPE,
            self::ACTION_TYPE
        );
        $this->addNewStatistics(
            self::ACTION_TYPE,
            self::ACTION_TYPE
        );
    }
}
