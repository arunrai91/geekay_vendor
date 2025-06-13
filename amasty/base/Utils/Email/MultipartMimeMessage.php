<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Utils\Email;

use Magento\Framework\Mail\MimeInterface;
use Magento\Framework\Mail\MimeMessageInterface;
use Magento\Framework\Mail\MimePart;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\Part\AbstractPart;
use Symfony\Component\Mime\Part\Multipart\MixedPart;
use Symfony\Component\Mime\Part\TextPart;

/**
 * Magento 2.4.8 doesn't allow to send mime message with anything beside one text part
 * Need to send Multipart message to add attachments
 */
class MultipartMimeMessage implements MimeMessageInterface
{
    /**
     * @var Message
     */
    private $mimeMessage;

    public function __construct(array $parts)
    {
        if (count($parts) > 1) {
            $mimeParts = array_map(static function (MimePart $part) {
                return $part->getMimePart();
            }, $parts);
            $body = new MixedPart(...$mimeParts);
        } else {
            $part = array_shift($parts);
            $body = $part->getMimePart();
        }
        $this->mimeMessage = new Message(null, $body);
    }

    /**
     * @return AbstractPart[]
     */
    public function getParts(): array
    {
        $parts = [];
        $body = $this->mimeMessage->getBody();
        if ($body instanceof MixedPart) {
            $parts = $body->getParts();
        } elseif ($body instanceof TextPart) {
            $parts[] = $body;
        }

        return $parts;
    }

    public function isMultiPart(): bool
    {
        $body = $this->mimeMessage->getBody();

        return $body instanceof MixedPart && count($body->getParts()) > 1;
    }

    public function getMessage(string $endOfLine = MimeInterface::LINE_END): string
    {
        return str_replace("\r\n", $endOfLine, $this->mimeMessage->toString());
    }

    public function getPartHeadersAsArray(int $partNum): array
    {
        $headersArray = [];
        $parts = $this->getParts();
        if (isset($parts[$partNum])) {
            $headers = $parts[$partNum]->getHeaders();
            foreach ($headers->toArray() as $header) {
                $headersArray[$header->getName()] = $header->getBodyAsString();
            }
        }

        return $headersArray;
    }

    public function getPartHeaders(int $partNum, string $endOfLine = MimeInterface::LINE_END): string
    {
        $parts = $this->getParts();
        if (isset($parts[$partNum])) {
            $headers = $parts[$partNum]->getHeaders();
            $headersString = $headers->toString();

            return str_replace("\r\n", $endOfLine, $headersString);
        }

        return '';
    }

    public function getPartContent(int $partNum, string $endOfLine = MimeInterface::LINE_END): string
    {
        $parts = $this->getParts();
        if (isset($parts[$partNum])) {
            $content = $parts[$partNum]->toString();

            return str_replace("\r\n", $endOfLine, $content);
        }

        return '';
    }

    public function getMimeMessage(): Message
    {
        return $this->mimeMessage;
    }
}
