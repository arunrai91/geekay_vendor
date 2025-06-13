<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Setup\Operation;

use Amasty\Feed\Model\Category\ResourceModel\Taxonomy;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\FlagManager;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\SampleData\Context as SampleDataContext;
use Magento\Framework\Setup\SampleData\FixtureManager;
use Psr\Log\LoggerInterface;

class UpgradeTo210 implements OperationInterface
{
    public const GOOGLE_CATEGORY = 'googlecategory';
    public const LOCALE_CODE_ID = 1;
    public const CHUNK_SIZE = 1000;
    public const AMASTY_FEED_GOOGLECATEGORY_INSERTED_FLAG = 'amasty_feed_googlecategory_inserted';
    /**
     * @var File
     */
    private $driverFile;

    /**
     * @var Csv
     */
    private $csv;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var FixtureManager
     */
    private $fixtureManager;

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Csv $csv,
        ResourceConnection $resource,
        File $driverFile,
        SampleDataContext $sampleDataContext,
        FlagManager $flagManager = null,
        LoggerInterface $logger = null
    ) {
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        $this->driverFile = $driverFile;
        $this->csv = $csv;
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->flagManager = $flagManager ?? ObjectManager::getInstance()->get(FlagManager::class);
        $this->logger = $logger ?? ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    public function execute(ModuleDataSetupInterface $moduleDataSetup, string $setupVersion): void
    {
        if (version_compare($setupVersion, '2.1.0', '<')
            || !$this->flagManager->getFlagData(self::AMASTY_FEED_GOOGLECATEGORY_INSERTED_FLAG)
        ) {
            // Workaround for DDL statements are not allowed in transactions
            $this->connection->delete($moduleDataSetup->getTable(Taxonomy::TABLE_NAME));
            try {
                $directoryPath = $this->getDirectoryPath();
                if ($this->driverFile->isExists($directoryPath)) {
                    $files = $this->driverFile->readDirectory($directoryPath);
                    foreach ($files as $file) {
                        if ($this->driverFile->isFile($file)) {
                            $this->insertGoogleCategories($file);
                        }
                    }
                }
                $this->flagManager->saveFlag(self::AMASTY_FEED_GOOGLECATEGORY_INSERTED_FLAG, true);
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
    }

    private function insertGoogleCategories(string $file): void
    {
        $googleCategories = $this->getGoogleCategories($file);
        $insertChunks = array_chunk($googleCategories, self::CHUNK_SIZE);
        foreach ($insertChunks as $chunk) {
            $this->connection->insertMultiple(
                $this->resource->getTableName(Taxonomy::TABLE_NAME),
                $chunk
            );
        }
    }

    /**
     * @return string
     */
    private function getDirectoryPath()
    {
        return $this->fixtureManager->getFixture('Amasty_Feed::fixtures/' . self::GOOGLE_CATEGORY);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function deleteEmptyItems($data)
    {
        return array_filter($data);
    }

    /**
     * @param string $data
     *
     * @return string|null
     */
    private function getLocaleCode($data)
    {
        $pattern = "/\.([a-z]{2,3}-([A-Za-z]{2,4}-)?[A-Z]{2})\.csv/";
        preg_match_all($pattern, $data, $match);

        return $match[self::LOCALE_CODE_ID][0] ?? null;
    }

    private function getGoogleCategories(string $file): array
    {
        $result = [];
        $csvData = $this->csv->getData($file);

        $languageCode = $this->getLocaleCode($file);
        if (null !== $languageCode) {
            foreach ($csvData as $row => $data) {
                array_shift($data);
                $newData = $this->deleteEmptyItems($data);
                $subcategories = implode(' > ', $newData);
                $result[$row] = [
                    'category' => $subcategories,
                    'language_code' => $languageCode
                ];
            }
        }

        return $result;
    }
}
