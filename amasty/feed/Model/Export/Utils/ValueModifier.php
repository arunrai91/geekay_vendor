<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Export\Utils;

use Amasty\Feed\Api\Data\FeedInterface;
use Amasty\Feed\Model\OptionSource\Feed\Modifier;
use Magento\Framework\Escaper;

class ValueModifier
{
    public const HTTP = 'http://';
    public const HTTPS = 'https://';

    public const PRICE_CURRENCY = 'price_currency';
    public const PRICE_CURRENCY_SHOW = 'price_currency_show';
    public const PRICE_DECIMALS = 'price_decimals';
    public const PRICE_DECIMAL_POINT = 'price_decimal_point';
    public const PRICE_THOUSAND_SEPARATOR = 'price_thousand_separator';

    /**
     * @var string
     */
    private $formatDate;

    /**
     * @var string[]
     */
    private $formatPriceOptions = [];

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var NumberFormat
     */
    private $numberFormat;

    public function __construct(
        Escaper $escaper,
        NumberFormat $numberFormat
    ) {
        $this->escaper = $escaper;
        $this->numberFormat = $numberFormat;
    }

    public function setFeedFormatOptions(FeedInterface $feed): void
    {
        $this->initPriceFormatOptions($feed);
        $this->formatDate = $feed->getFormatDate() ?: 'Y-m-d';
    }

    /**
     * @param mixed $value
     * @param string $modifier
     * @param string|null $arg0
     * @param string|null $arg1
     *
     * @return mixed
     */
    public function modify($value, string $modifier, ?string $arg0 = null, ?string $arg1 = null)
    {
        switch ($modifier) {
            case Modifier::STRIP_TAGS:
                $value = $this->fullRemoveTags((string)$value);
                break;
            case Modifier::GOOGLE_HTML_ESCAPE:
                $value = $this->googleEscapeHtml((string)$value);
                break;
            case Modifier::HTML_ESCAPE:
                $value = (string)$this->escaper->escapeHtml($value);
                break;
            case Modifier::REMOVE_WIDGET_HTML:
                $value = $this->removeWidgetAndConfig((string)$value);
                break;
            case Modifier::LOWERCASE:
                $value = $this->lowerCase((string)$value);
                break;
            case Modifier::INTEGER:
                $value = (int)$value;
                break;
            case Modifier::LENGTH:
                $length = (int)$arg0;
                if ($arg0) {
                    $value = function_exists('mb_substr')
                        ? mb_substr((string)$value, 0, $length, 'UTF-8')
                        : (string)substr((string)$value, 0, $length);
                }
                break;
            case Modifier::PREPEND:
                $value = $arg0 . $value;
                break;
            case Modifier::APPEND:
                $value .= $arg0;
                break;
            case Modifier::PREPEND_IF_NOT_EMPTY:
                if ($value) {
                    $value = $arg0 . $value;
                }
                break;
            case Modifier::APPEND_IF_NOT_EMPTY:
                if ($value) {
                    $value .= $arg0;
                }
                break;
            case Modifier::REPLACE:
                $value = str_replace($arg0, $arg1, (string)$value);
                break;
            case Modifier::UPPERCASE:
                $value = function_exists('mb_strtoupper')
                    ? (string)mb_strtoupper((string)$value, 'UTF-8')
                    : strtoupper((string)$value);
                break;
            case Modifier::CAPITALIZE_FIRST:
                $value = ucfirst($this->lowerCase((string)$value));
                break;
            case Modifier::CAPITALIZE_EACH_WORD:
                $value = ucwords($this->lowerCase((string)$value));
                break;
            case Modifier::ROUND:
                if (is_numeric($value)) {
                    $value = round((float)$value);
                }
                break;
            case Modifier::IF_EMPTY:
                if ($value === '') {
                    $value = (string)$arg0;
                }
                break;
            case Modifier::IF_NOT_EMPTY:
                if ($value !== '') {
                    $value = (string)$arg0;
                }
                break;
            case Modifier::FULL_IF_NOT_EMPTY:
                if ($value === '') {
                    $value = (string)$arg0;
                } else {
                    $value = (string)$arg1;
                }
                break;
            case Modifier::TO_SECURE_URL:
                $this->replaceFirst($value, self::HTTP, self::HTTPS);
                break;
            case Modifier::TO_UNSECURE_URL:
                $this->replaceFirst($value, self::HTTPS, self::HTTP);
                break;
        }

        return $value;
    }

    public function formatValue(array $field, $value)
    {
        $format = $field['format'] ?? 'as_is';
        switch ($format) {
            case 'integer':
            case 'as_is':
                break;
            case 'date':
                if (!empty($value)) {
                    $value = date($this->formatDate, strtotime((string)$value));
                }
                break;
            case 'price':
                if (is_numeric($value)) {
                    $value = number_format(
                        (float)$value,
                        (int)$this->formatPriceOptions[self::PRICE_DECIMALS],
                        $this->formatPriceOptions[self::PRICE_DECIMAL_POINT],
                        $this->formatPriceOptions[self::PRICE_THOUSAND_SEPARATOR]
                    );
                    if ($this->formatPriceOptions[self::PRICE_CURRENCY_SHOW]
                        && $this->formatPriceOptions[self::PRICE_CURRENCY]
                    ) {
                        $value .= ' ' . $this->formatPriceOptions[self::PRICE_CURRENCY];
                    }
                }
                break;
        }

        return $value;
    }

    private function initPriceFormatOptions(FeedInterface $feed): void
    {
        $decimals = $this->numberFormat->getAllDecimals();
        $separators = $this->numberFormat->getAllSeparators();

        $formatPriceDecimals = $feed->getFormatPriceDecimals();
        $formatPriceDecimalPoint = $feed->getFormatPriceDecimalPoint();
        $formatPriceThousandsSeparator = $feed->getFormatPriceThousandsSeparator();

        $this->formatPriceOptions[self::PRICE_CURRENCY] = $feed->getFormatPriceCurrency();
        $this->formatPriceOptions[self::PRICE_CURRENCY_SHOW] = (int)$feed->getFormatPriceCurrencyShow() === 1;
        $this->formatPriceOptions[self::PRICE_DECIMALS] = $decimals[$formatPriceDecimals] ?? 2;
        $this->formatPriceOptions[self::PRICE_DECIMAL_POINT] = $separators[$formatPriceDecimalPoint] ?? '.';
        $this->formatPriceOptions[self::PRICE_THOUSAND_SEPARATOR] = $separators[$formatPriceThousandsSeparator] ?? ',';
    }

    private function fullRemoveTags(string $value): string
    {
        $value = $this->removeTagContentAndAttribute($value);
        $value = strtr($value, ["\n" => '', "\r" => '']);

        return strip_tags($value);
    }

    private function googleEscapeHtml(string $value): string
    {
        $value = $this->removeTagContentAndAttribute($value);

        return $this->escaper->escapeHtml(trim($value));
    }

    private function removeTagContentAndAttribute(string $value): string
    {
        // Remove HTML tags with content
        foreach (['style', 'canvas', 'script'] as $tag) {
            $value = preg_replace('/(<' . $tag . '.*?>.*?<\/' . $tag . '>)/is', '', $value);
            $value = preg_replace('/(<' . $tag . ' .*?\/>)/is', '', $value);
        }

        // Remove all attributes from HTML tags
        return preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/si", '<$1$2>', $value);
    }

    private function removeWidgetAndConfig(string $value): string
    {
        return preg_replace('/\{\{([\s\S]+?)}}/', '', $value);
    }

    private function replaceFirst(string &$value, string $origin, string $replace): void
    {
        if (strpos($value, $origin) === 0) {
            $value = substr_replace($value, $replace, 0, strlen($origin));
        }
    }

    private function lowerCase(string $value): string
    {
        return function_exists('mb_strtolower')
            ? mb_strtolower($value, 'UTF-8')
            : strtolower($value);
    }
}
