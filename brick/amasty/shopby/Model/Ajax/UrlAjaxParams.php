<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Ajax;

use Magento\Framework\Url\Decoder;
use Magento\Framework\Url\Encoder;

class UrlAjaxParams
{
    /**
     * @var Encoder
     */
    private $urlEncoder;

    /**
     * @var Decoder
     */
    private $urlDecoder;

    public function __construct(
        Encoder $urlEncoder,
        Decoder $urlDecoder
    ) {
        $this->urlEncoder = $urlEncoder;
        $this->urlDecoder = $urlDecoder;
    }

    /**
     * @param array $responseData
     * @return array
     */
    public function removeEncodedAjaxParams(array $responseData)
    {
        $pattern = '@aHR0c(Dov|HM6)[A-Za-z0-9_-]+@u';
        array_walk($responseData, function (&$html) use ($pattern) {
            // 'aHR0cDov' and 'aHR0cHM6' are the beginning of the Base64 code for 'http:/' and 'https:'
            $res = preg_replace_callback($pattern, [$this, 'removeAjaxParamFromEncodedMatch'], $html);
            if ($res !== null) {
                $html = $res;
            }
        });

        return $responseData;
    }

    private function removeAjaxParamFromEncodedMatch(array $match): string
    {
        $originalUrl = $this->urlDecoder->decode($match[0]);
        if (!$originalUrl) {
            return $match[0];
        }
        $url = $this->removeAjaxParam($originalUrl);

        return ($originalUrl === $url) ? $match[0] : rtrim($this->urlEncoder->encode($url), ',');
    }

    /**
     * Remove amasty AJAX ILN flag from HTTP query
     *
     * Note: covered by Unit test
     * @see \Amasty\Shopby\Test\Unit\Model\Ajax\UrlAjaxParamsTest::testRemoveAjaxParam
     *
     * @param string|array $data
     * @return array|string|string[]
     */
    public function removeAjaxParam($data)
    {
        return str_replace([
            '?shopbyAjax=1&amp;',
            '?shopbySorting=1&amp;',
            '?shopbyAjax=1&',
            '?shopbySorting=1&',
            '?shopbyAjax=1',
            '?shopbySorting=1',
            '&amp;shopbyAjax=1',
            '&amp;shopbySorting=1',
            '&shopbyAjax=1',
            '&shopbySorting=1',
        ], ['?', '?', '?', '?', '', '', '', '', '', ''], $data);
    }
}
