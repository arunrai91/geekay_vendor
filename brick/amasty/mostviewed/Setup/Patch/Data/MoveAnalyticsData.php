<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Setup\Patch\Data;

use Amasty\Mostviewed\Model\Analytics\Collector;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;

class MoveAnalyticsData implements DataPatchInterface
{
    /**
     * @var Collector
     */
    private $collector;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        Collector $collector,
        LoggerInterface $logger
    ) {
        $this->collector = $collector;
        $this->logger = $logger;
    }

    /**
     * @return string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return MoveAnalyticsData
     */
    public function apply()
    {
        try {
            $this->collector->execute();
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $this;
    }
}
