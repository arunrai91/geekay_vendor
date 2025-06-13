<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Setup\Patch\Data;

use Amasty\Feed\Setup\Operation\ImportFeedTemplates;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;

class FeedTemplates implements DataPatchInterface
{
    /**
     * @var string[]
     */
    private $templateFixtures = [
        'shopping',
        'bing',
        'google',
        'amazon_product',
        'amazon_inventory',
        'amazon_price',
        'amazon_image'
    ];

    /**
     * @var ImportFeedTemplates
     */
    private $importFeedTemplates;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        ImportFeedTemplates $importFeedTemplates,
        LoggerInterface $logger
    ) {
        $this->importFeedTemplates = $importFeedTemplates;
        $this->logger = $logger;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): FeedTemplates
    {
        try {
            $this->importFeedTemplates->import($this->templateFixtures, 'Amasty_Feed::fixtures/');
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        return $this;
    }
}
