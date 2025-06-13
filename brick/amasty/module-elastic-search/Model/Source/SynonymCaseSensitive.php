<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class SynonymCaseSensitive implements OptionSourceInterface
{
    public const YES = 1;
    public const NO = 0;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::YES, 'label' => __('Yes (default state)')],
            ['value' => self::NO, 'label' => __('No')]
        ];
    }
}
