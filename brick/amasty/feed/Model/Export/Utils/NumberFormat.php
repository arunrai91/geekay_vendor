<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Export\Utils;

class NumberFormat
{
    /**
     * Points constants
     */
    public const ONE_POINT = 'one';
    public const TWO_POINTS = 'two';
    public const THREE_POINTS = 'three';
    public const FOUR_POINTS = 'four';

    /**
     * Separate constants
     */
    public const DOT = 'dot';
    public const COMMA = 'comma';
    public const SPACE = 'space';
    public const WITHOUT_SEPARATOR = 'empty';

    public function getAllDecimals(): array
    {
        return [
            self::ONE_POINT => 1,
            self::TWO_POINTS => 2,
            self::THREE_POINTS => 3,
            self::FOUR_POINTS => 4
        ];
    }

    public function getAllSeparators(): array
    {
        return [
            self::DOT => '.',
            self::COMMA => ',',
            self::SPACE => ' ',
            self::WITHOUT_SEPARATOR => '',
        ];
    }
}
