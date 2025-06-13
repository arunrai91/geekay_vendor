<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Plugin\Sales\Model\ResourceModel\Order\Grid\Collection;

use Amasty\Mostviewed\Model\ConfigProvider;
use Amasty\Mostviewed\Model\ResourceModel\Pack\Sales\AggregatedByPackTable\JoinProcessor;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;

class JoinPackData
{
    /**
     * @var JoinProcessor
     */
    private $joinProcessor;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(JoinProcessor $joinProcessor, ConfigProvider $configProvider)
    {
        $this->joinProcessor = $joinProcessor;
        $this->configProvider = $configProvider;
    }

    public function beforeLoad(Collection $subject): void
    {
        if ($this->configProvider->isJoinPackDataToSalesGrid()) {
            $this->joinProcessor->execute($subject);
        }
    }
}
