<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Utils\Text;

class Splitter
{
    /**
     * @param string $text
     * @param int $maxLength
     * @return string[]
     */
    public function splitByMaxLength(string $text, int $maxLength = 300): array
    {
        $text = $this->normalizeNewlineCharacters($text);

        if (empty($text)) {
            return ['', ''];
        }

        if (mb_strlen($text) <= $maxLength) {
            return [$text, ''];
        }

        $lastSpacePosition = strrpos(substr($text, 0, $maxLength), ' ');
        $lastNewlinePosition = strrpos(substr($text, 0, $maxLength), "\n");

        if ($lastSpacePosition === false && $lastNewlinePosition === false) {
            // No space or newline found; return text as the first string
            return [$text, ''];
        }

        $positionToSplit = max($lastSpacePosition, $lastNewlinePosition);
        $firstPart = substr($text, 0, $positionToSplit);
        $secondPart = substr($text, $positionToSplit);

        return [$firstPart, $secondPart];
    }

    private function normalizeNewlineCharacters(string $string): string
    {
        return trim(str_replace(["\r\n", "\n\r", "\r"], "\n", $string));
    }
}
