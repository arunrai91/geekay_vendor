<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Tokenizer implements OptionSourceInterface
{
    public const WHITESPACE = 'whitespace';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::WHITESPACE,
                'label' => __('Whitespace')
            ]
        ];
    }
}
