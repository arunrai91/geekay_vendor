<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Analytics\Collector\Click;

use Amasty\Mostviewed\Api\Data\ClickInterface;
use Amasty\Mostviewed\Model\Analytics\Collector\AbstractCollector;

class Block extends AbstractCollector
{
    public const ACTION_TYPE = 'click';
    public const ANALYTICS_TYPE = 'click_block';

    /**
     * Collect click block analytics actions
     */
    public function execute(): void
    {
        $this->updateAnalytics(
            self::ACTION_TYPE,
            self::ANALYTICS_TYPE,
            ClickInterface::CLICK_TYPE_BLOCK
        );
        $this->addNewStatistics(
            self::ACTION_TYPE,
            self::ANALYTICS_TYPE,
            ClickInterface::CLICK_TYPE_BLOCK
        );
    }
}
