<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Setup\Patch\Data;

use Amasty\Feed\Api\Data\FeedInterface;
use Amasty\Feed\Model\ResourceModel\Feed as FeedResource;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;

class RemoveTemplatesInEntityTable implements DataPatchInterface
{
    /**
     * @var string[]
     */
    private $templates = [
        'Amazon Product',
        'Amazon Inventory',
        'Amazon Price',
        'Amazon Image',
        'Google',
        'Bing',
        'Shopping'
    ];

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    public static function getDependencies(): array
    {
        return [
            FeedTemplates::class
        ];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): RemoveTemplatesInEntityTable
    {

        try {
            $this->updateEntityTable();
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        return $this;
    }

    private function updateEntityTable(): void
    {
        // Remove templates in entity table.
        $connection = $this->resourceConnection->getConnection();
        $connection->delete(
            $this->resourceConnection->getTableName(FeedResource::TABLE_NAME),
            [
                FeedInterface::IS_TEMPLATE . ' = 1',
                FeedInterface::NAME . ' IN (?)' => $this->templates,
            ]
        );
        // To save custom templates move them to feed entity.
        $connection->update(
            $this->resourceConnection->getTableName(FeedResource::TABLE_NAME),
            [FeedInterface::IS_TEMPLATE => 0],
            [FeedInterface::IS_TEMPLATE . ' = 1']
        );
    }
}
