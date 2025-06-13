<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Feed;

use Amasty\Feed\Api\Data\FeedInterface;
use Magento\Framework\UrlInterface;

class UrlProvider
{
    /**
     * @var UrlInterface
     */
    private UrlInterface $url;

    public function __construct(
        UrlInterface $url
    ) {
        $this->url = $url;
    }

    public function get(FeedInterface $feed): string
    {
        return $this->url
            ->setScope($feed->getStoreId())
            ->getUrl(
                '',
                [
                    '_direct' => 'amfeed/feed/download',
                    '_query' => [
                        'id' => $feed->getEntityId()
                    ]
                ]
            );
    }
}
