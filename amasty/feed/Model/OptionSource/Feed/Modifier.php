<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\OptionSource\Feed;

use Magento\Framework\Data\OptionSourceInterface;

class Modifier implements OptionSourceInterface
{
    public const STRIP_TAGS = 'strip_tags';
    public const HTML_ESCAPE = 'html_escape';
    public const GOOGLE_HTML_ESCAPE = 'google_html_escape';
    public const REMOVE_WIDGET_HTML = 'remove_widget_html';
    public const UPPERCASE = 'uppercase';
    public const CAPITALIZE_FIRST = 'capitalize';
    public const CAPITALIZE_EACH_WORD = 'capitalize_each_word';
    public const LOWERCASE = 'lowercase';
    public const INTEGER = 'integer';
    public const LENGTH = 'length';
    public const PREPEND = 'prepend';
    public const APPEND = 'append';
    public const PREPEND_IF_NOT_EMPTY = 'prepend_if_not_empty';
    public const APPEND_IF_NOT_EMPTY = 'append_if_not_empty';
    public const REPLACE = 'replace';
    public const ROUND = 'round';
    public const IF_EMPTY = 'if_empty';
    public const IF_NOT_EMPTY = 'if_not_empty';
    public const FULL_IF_NOT_EMPTY = 'full_if_not_empty';
    public const TO_SECURE_URL = 'to_secure_url';
    public const TO_UNSECURE_URL = 'to_unsecure_url';

    public function toOptionArray(): array
    {
        $result = [];

        foreach ($this->toArray() as $value => $label) {
            $result[] = ['value' => $value, 'label' => $label];
        }

        return $result;
    }

    public function toArray(): array
    {
        return [
            self::STRIP_TAGS => __('Strip Tags'),
            self::HTML_ESCAPE => __('Html Escape'),
            self::GOOGLE_HTML_ESCAPE => __('Google Html Escape'),
            self::REMOVE_WIDGET_HTML => __('Remove Widget Html'),
            self::UPPERCASE => __('Uppercase'),
            self::CAPITALIZE_FIRST => __('Capitalize'),
            self::CAPITALIZE_EACH_WORD => __('Capitalize Each Word'),
            self::LOWERCASE => __('Lowercase'),
            self::INTEGER => __('Integer'),
            self::LENGTH => __('Length'),
            self::PREPEND => __('Prepend'),
            self::APPEND => __('Append'),
            self::PREPEND_IF_NOT_EMPTY => __('Prepend (If not empty)'),
            self::APPEND_IF_NOT_EMPTY => __('Append (If not empty)'),
            self::REPLACE => __('Replace'),
            self::ROUND => __('Round'),
            self::IF_EMPTY => __('If Empty'),
            self::IF_NOT_EMPTY => __('If Not Empty'),
            self::FULL_IF_NOT_EMPTY => __('If Empty/Not Empty'),
            self::TO_SECURE_URL => __('To secure URL'),
            self::TO_UNSECURE_URL => __('To unsecure URL')
        ];
    }
}
