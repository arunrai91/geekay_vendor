<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model;

use Amasty\Feed\Api\Data\FeedInterface;
use Amasty\Feed\Model\Config\Source\FeedStatus;
use Amasty\Feed\Model\Config\Source\StorageFolder;
use Amasty\Feed\Model\Export\Adapter\AdapterProvider;
use Amasty\Feed\Model\Export\Product\Attributes\ProductFeedAttributesPool;
use Amasty\Feed\Model\Export\ProductFactory;
use Amasty\Feed\Model\Filesystem\FeedOutput;
use Amasty\Feed\Model\OptionSource\Feed\ParentFlag;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\ImportExport\Model\Export\Adapter\AbstractAdapter;
use Psr\Log\LoggerInterface;

class FeedExport
{
    /**
     * @var Export\ProductFactory
     */
    private $productExportFactory;

    /**
     * @var Export\Adapter\AdapterProvider
     */
    private $adapterProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var FeedRepository
     */
    private $feedRepository;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var FeedOutput
     */
    private $feedOutput;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var bool
     */
    private $multiProcessMode;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ProductFeedAttributesPool
     */
    private $feedAttributesPool;

    public function __construct(
        ProductFactory $productExportFactory,
        AdapterProvider $adapterProvider,
        FeedRepository $feedRepository,
        ManagerInterface $eventManager,
        FeedOutput $feedOutput,
        Config $config,
        LoggerInterface $logger,
        Filesystem $filesystem,
        ProductFeedAttributesPool $feedAttributesPool,
        bool $multiProcessMode = false
    ) {
        $this->productExportFactory = $productExportFactory;
        $this->adapterProvider = $adapterProvider;
        $this->logger = $logger;
        $this->feedRepository = $feedRepository;
        $this->eventManager = $eventManager;
        $this->feedOutput = $feedOutput;
        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->feedAttributesPool = $feedAttributesPool;
        $this->multiProcessMode = $multiProcessMode;
    }

    public function export(
        FeedInterface $feed,
        int $page,
        array $productIds,
        bool $lastPage,
        bool $preview = false,
        string $cronGeneratedTime = ''
    ): string {
        $fileName = $this->multiProcessMode
            ? $this->getChunkFileName($feed, $page)
            : $feed->getFilename();

        $productExport = $this->productExportFactory->create(
            ['storeId' => $feed->getStoreId(), 'feedProfile' => $feed]
        );
        $result = $productExport
            ->setPage((int)$page)
            ->setWriter($this->getWriter($feed, $fileName, $this->multiProcessMode ? 0 : $page))
            ->setAttributes($this->getAttributes($feed))
            ->setParentAttributes($this->getAttributes($feed, true))
            ->setMatchingProductIds($productIds)
            ->export($lastPage);

        if ($preview) {
            $this->feedOutput->delete($feed);
        } else {
            $feed->setGeneratedAt($cronGeneratedTime ?: date('Y-m-d H:i:s'));
            $feed->setProductsAmount($feed->getProductsAmount() + count($productIds));

            $status = $lastPage && !$this->multiProcessMode
                ? FeedStatus::READY
                : FeedStatus::PROCESSING;
            $feed->setStatus($status);
            $this->feedRepository->save($feed);
            $this->processAmfeedExportEnd($feed);
        }

        return $result;
    }

    public function combineChunks(FeedInterface $feed): void
    {
        if ($this->config->getStorageFolder() === StorageFolder::VAR_FOLDER) {
            $dir = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        } else {
            $dir = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        }
        $targetDirectory = trim($this->config->getFilePath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $firstFileName = $targetDirectory . $this->getChunkFileName($feed, 0);
        if (!$dir->isExist($firstFileName)) {
            return;
        }
        $content = $dir->readFile($firstFileName);
        $chunk = 1;
        while ($dir->isExist($fileName = $targetDirectory . $this->getChunkFileName($feed, $chunk++))) {
            $content .= $dir->readFile($fileName);
            $dir->delete($fileName);
        }
        $dir->writeFile($firstFileName, $content);
        $dir->renameFile($firstFileName, $targetDirectory. $feed->getFilename());
    }

    public function processAmfeedExportEnd(FeedInterface $feed): void
    {
        if ((int)$feed->getStatus() === FeedStatus::READY) {
            $this->feedOutput->get($feed);
            $this->eventManager->dispatch('amfeed_export_end', ['feed' => $feed]);
        }
    }

    /**
     * @throws LocalizedException
     */
    private function getWriter(FeedInterface $feed, string $filename, int $page): AbstractAdapter
    {
        try {
            $destination = trim($this->config->getFilePath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
            $writer = $this->adapterProvider->get(
                $feed->getFeedType(),
                [
                    'destination' => $destination,
                    'page' => $page
                ]
            )->initBasics($feed);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new LocalizedException(__('Please correct the file format.'));
        }

        return $writer;
    }

    private function getAttributes(FeedInterface $feed, bool $parent = false): array
    {
        $attributes = array_fill_keys(array_keys(array_flip($this->feedAttributesPool->getAll())), []);
        if ($feed->isCsv()) {
            $this->prepareCsvAttributes($feed, $attributes, $parent);
        } elseif ($feed->isXml()) {
            $this->prepareXmlAttributes($feed, $attributes, $parent);
        }

        return $attributes;
    }

    private function prepareCsvAttributes(FeedInterface $feed, array &$attributes, bool $parent): void
    {
        $parentOptions = [ParentFlag::YES, ParentFlag::YES_IF_EMPTY, ParentFlag::YES_STRICT];
        foreach ($feed->getCsvField() as $field) {
            if (($parent && isset($field['parent']) && in_array($field['parent'], $parentOptions, true))
                || (!$parent && isset($field['attribute']))
            ) {
                [$type, $code] = explode("|", $field['attribute']);
                if (array_key_exists($type, $attributes)) {
                    $attributes[$type][$code] = $code;
                }
            }
        }
    }

    private function prepareXmlAttributes(FeedInterface $feed, array &$attributes, bool $parent): void
    {
        $regex = "#{(.*?)}#";
        preg_match_all($regex, $feed->getXmlContent(), $vars);
        $parentOptions = [ParentFlag::YES, ParentFlag::YES_IF_EMPTY, ParentFlag::YES_STRICT];
        if (isset($vars[1])) {
            foreach ($vars[1] as $attributeRow) {
                preg_match("/attribute=\"(.*?)\"/", $attributeRow, $attrReg);
                preg_match("/parent=\"(.*?)\"/", $attributeRow, $parentReg);

                if (isset($attrReg[1])) {
                    [$type, $code] = explode("|", $attrReg[1]);
                    $attributeParent = $parentReg[1] ?? ParentFlag::NO;
                    if (!$parent || in_array($attributeParent, $parentOptions, true)) {
                        if (array_key_exists($type, $attributes)) {
                            $attributes[$type][$code] = $code;
                        }
                    }
                }
            }
        }
    }

    private function getChunkFileName(FeedInterface $feed, int $page): string
    {
        return $feed->getFilename() . '_' . $page;
    }
}
