<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Feed;

use Amasty\Base\Model\Serializer;
use Amasty\Feed\Api\Data\FeedInterface;
use Amasty\Feed\Api\Data\FeedTemplateInterface;

class ContentProcessor
{
    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        Serializer $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * @return string[]
     */
    public function getContent(FeedTemplateInterface $template): array
    {
        $content = $this->serializer->unserialize($template->getTemplateContent());
        // Temporary solution. Remove placeholders with the following format "< placeholder: any text >".
        switch ($template->getTemplateType()) {
            case 'xml':
                $content[FeedInterface::XML_CONTENT] = $this->replace(
                    $content[FeedInterface::XML_CONTENT] ?? '',
                    ''
                );
                break;
            case 'csv':
            case 'txt':
                $content[FeedInterface::CSV_FIELD] = $this->replace(
                    $content[FeedInterface::CSV_FIELD] ?? '',
                    ''
                );
                break;
        }

        return $content;
    }

    private function replace(string $contentBody, string $toReplace): string
    {
        return preg_replace(
            '/\s*<(\splaceholder:[^>]*)[^\s>]*\s>/i',
            $toReplace,
            $contentBody
        );
    }
}
