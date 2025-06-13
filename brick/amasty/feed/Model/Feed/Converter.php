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

class Converter
{
    /**
     * @var ContentProcessor
     */
    private $contentProcessor;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        Serializer $serializer,
        ContentProcessor $contentProcessor
    ) {
        $this->serializer = $serializer;
        $this->contentProcessor = $contentProcessor;
    }

    public function convertTemplateToFeed(FeedTemplateInterface $template, FeedInterface $feed): FeedInterface
    {
        $feed->setName($template->getTemplateName());
        $feed->setFeedType($template->getTemplateType());
        $feed->setTemplateCode($template->getTemplateCode());

        $config = $this->serializer->unserialize($template->getTemplateConfig());
        $this->addData($config, $feed);

        $content = $this->contentProcessor->getContent($template);
        $this->addData($content, $feed);

        return $feed;
    }

    private function addData(array $data, FeedInterface $feedModel): void
    {
        foreach ($data as $key => $param) {
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));
            $feedModel->$method($param);
        }
    }
}
