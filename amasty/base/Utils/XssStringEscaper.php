<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Utils;

use Psr\Log\LoggerInterface;

/**
 * Escape javascript and prohibited tags from html content
 * @see \Magento\Framework\Escaper
 */
class XssStringEscaper
{
    private const NOT_ALLOWED_TAGS = ['script', 'img', 'embed', 'iframe', 'video', 'source', 'object', 'audio'];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private static $xssFiltrationPattern =
        '/((javascript(\\\\x3a|:|%3A))|(data(\\\\x3a|:|%3A))|(vbscript:))|'
        . '((\\\\x6A\\\\x61\\\\x76\\\\x61\\\\x73\\\\x63\\\\x72\\\\x69\\\\x70\\\\x74(\\\\x3a|:|%3A))|'
        . '(\\\\x64\\\\x61\\\\x74\\\\x61(\\\\x3a|:|%3A)))/i';

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function escapeScriptInHtml(?string $data): string
    {
        if (empty($data)) {
            return '';
        }

        $domDocument = new \DOMDocument('1.0', 'UTF-8');
        $wrapperElementId = uniqid();
        $data = $this->escapeScriptIdentifiers($this->prepareUnescapedCharacters($data));
        $convmap = [0x80, 0x10FFFF, 0, 0x1FFFFF];
        $string = mb_encode_numericentity(
            $data,
            $convmap,
            'UTF-8'
        );

        try {
            $domDocument->loadHTML(
                '<html><body id="' . $wrapperElementId . '">' . $string . '</body></html>'
            );
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        $this->removeNotAllowedTags($domDocument);

        $result = mb_decode_numericentity(
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
            html_entity_decode(
                $domDocument->saveHTML(),
                ENT_QUOTES|ENT_SUBSTITUTE,
                'UTF-8'
            ),
            $convmap,
            'UTF-8'
        );

        preg_match('/<body id="' . $wrapperElementId . '">(.+)<\/body><\/html>$/si', $result, $matches);

        return !empty($matches) ? $matches[1] : '';
    }

    /**
     * Remove `javascript:`, `vbscript:`, `data:` words from the string.
     */
    private function escapeScriptIdentifiers(string $data): string
    {
        $filteredData = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $data);
        if ($filteredData === false || $filteredData === '') {
            return '';
        }

        $filteredData = preg_replace(self::$xssFiltrationPattern, ':', $filteredData);
        if ($filteredData === false) {
            return '';
        }

        if (preg_match(self::$xssFiltrationPattern, $filteredData)) {
            $filteredData = $this->escapeScriptIdentifiers($filteredData);
        }

        return $filteredData;
    }

    /**
     * Used to replace characters, that mb_convert_encoding will not process
     *
     * @param string $data
     * @return string|null
     */
    private function prepareUnescapedCharacters(string $data): ?string
    {
        $patterns = ['/\&/u'];
        $replacements = ['&amp;'];
        return \preg_replace($patterns, $replacements, $data);
    }

    /**
     * Remove not allowed tags
     *
     * @param \DOMDocument $domDocument
     * @return void
     */
    private function removeNotAllowedTags(\DOMDocument $domDocument): void
    {
        $xpath = new \DOMXPath($domDocument);
        $nodes = $xpath->query(
            '//node()[name() = \''
            . implode('\' or name() = \'', self::NOT_ALLOWED_TAGS)
            . '\']'
        );

        foreach ($nodes as $node) {
            $node->parentNode->removeChild($node);
        }
    }
}
