<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\FilterDataLoader;

use Amasty\Base\Model\ConfigProviderAbstract;

class ConfigReader extends ConfigProviderAbstract
{
    public const FILTER_SUFFIX = '_filter/';
    public const FILTER_BLOCK_POSITION = 'block_position';
    public const FILTER_POSITION_TOP = 'top_position';
    public const FILTER_POSITION_SIDE = 'side_position';
    public const FILTER_POSITION = 'position';
    public const FILTER_TOOLTIP = 'tooltip';
    public const FILTER_EXPAND_VALUE = 'is_expanded';

    /**
     * @var string
     */
    protected $pathPrefix = 'amshopby/';

    /**
     * Retrieve block_position setting value
     *
     * @param string $filterCode
     * @return int
     */
    public function getBlockPosition(string $filterCode): int
    {
        return (int) $this->getValue($filterCode . self::FILTER_SUFFIX . self::FILTER_BLOCK_POSITION);
    }

    /**
     * Retrieve top_position setting value
     *
     * @param string $filterCode
     * @return int
     */
    public function getPositionTop(string $filterCode): int
    {
        return (int) $this->getValue($filterCode . self::FILTER_SUFFIX . self::FILTER_POSITION_TOP);
    }

    /**
     * Retrieve side_position setting value
     *
     * @param string $filterCode
     * @return int
     */
    public function getPositionSide(string $filterCode): int
    {
        return (int) $this->getValue($filterCode . self::FILTER_SUFFIX . self::FILTER_POSITION_SIDE);
    }

    /**
     * Retrieve position setting value
     *
     * @param string $filterCode
     * @return int
     */
    public function getPosition(string $filterCode): int
    {
        return (int) $this->getValue($filterCode . self::FILTER_SUFFIX . self::FILTER_POSITION);
    }

    /**
     * Retrieve tooltip text setting value
     *
     * @param string $filterCode
     * @return string
     */
    public function getTooltip(string $filterCode): string
    {
        return (string) $this->getValue($filterCode . self::FILTER_SUFFIX . self::FILTER_TOOLTIP);
    }

    /**
     * Retrieve is_expanded setting value
     *
     * @param string $filterCode
     * @return int
     */
    public function getExpandValue(string $filterCode): int
    {
        return (int) $this->getValue($filterCode . self::FILTER_SUFFIX . self::FILTER_EXPAND_VALUE);
    }
}
