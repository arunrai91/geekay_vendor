<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Setup\Operation;

use Amasty\Base\Model\Serializer as BaseSerializer;
use Amasty\Feed\Api\Data\FeedTemplateInterfaceFactory;
use Amasty\Feed\Model\FeedTemplate;
use Amasty\Feed\Model\FeedTemplate\Repository;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Framework\Setup\SampleData\Context as SampleDataContext;
use Magento\Framework\Setup\SampleData\FixtureManager;
use Psr\Log\LoggerInterface;

class ImportFeedTemplates
{
    /**
     * @var string[]
     */
    private $excludeList;

    /**
     * @var array [ 'template_type' => [ 'field', ... ], ... ]
     */
    private $feedContent;

    /**
     * @var Serialize
     */
    private $serializer;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var BaseSerializer
     */
    private $baseSerializer;

    /**
     * @var FixtureManager
     */
    private $fixtureManager;

    /**
     * @var FeedTemplateInterfaceFactory
     */
    private $templateFactory;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        SampleDataContext $sampleDataContext,
        Serialize $serializer,
        BaseSerializer $baseSerializer,
        Filesystem $filesystem,
        FeedTemplateInterfaceFactory $templateFactory,
        Repository $repository,
        LoggerInterface $logger,
        array $excludeList,
        array $feedContent
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->serializer = $serializer;
        $this->baseSerializer = $baseSerializer;
        $this->filesystem = $filesystem;
        $this->templateFactory = $templateFactory;
        $this->repository = $repository;
        $this->logger = $logger;
        $this->excludeList = $excludeList;
        $this->feedContent = $feedContent;
    }

    /**
     * @param array $templateFixtures ['fixture_name', ...]
     */
    public function import(array $templateFixtures, string $fixtureDir): void
    {
        $dir = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);
        foreach ($templateFixtures as $fixture) {
            $path = $fixtureDir . $fixture;
            $fileName = $dir->getRelativePath($this->fixtureManager->getFixture($path));
            if (!$dir->isExist($fileName)) {
                continue;
            }

            try {
                $content = $dir->readFile($fileName);
            } catch (FileSystemException $exception) {
                $this->logger->error($exception->getMessage());
                continue;
            }

            try {
                $template = $this->repository->getBy($fixture, FeedTemplate::TEMPLATE_CODE);
            } catch (NoSuchEntityException $exception) {
                $template = $this->templateFactory->create();
            }

            $data = $this->prepareTemplateData($content);
            if ($data) {
                $template->addData($data);
                try {
                    $this->repository->save($template);
                } catch (LocalizedException $exception) {
                    $this->logger->error($exception->getMessage());
                }
            }
        }
    }

    private function prepareTemplateData(string $content): array
    {
        $result = [];
        $data = $this->serializer->unserialize(rtrim($content));
        if (is_array($data)) {
            if (isset($data['csv_field'])) {
                $data['csv_field'] = $this->convertCsvFieldSerialization($data['csv_field']);
            }
            $result[FeedTemplate::TEMPLATE_CODE] = $data['template_code'];
            $result[FeedTemplate::TEMPLATE_NAME] = $data['name'];
            $result[FeedTemplate::TEMPLATE_TYPE] = $data['feed_type'];
            $result[FeedTemplate::DYNAMIC_FIELDS] = $data['dynamic_fields'] ?? '';

            $templateContent = [];
            foreach (($this->feedContent[$data['feed_type']] ?? []) as $item) {
                if (isset($data[$item])) {
                    $templateContent[$item] = $data[$item];
                }
            }
            $result[FeedTemplate::TEMPLATE_CONTENT] = $this->baseSerializer->serialize($templateContent);

            // Add to config only unused fields.
            foreach ($this->excludeList as $field) {
                unset($data[$field]);
            }
            $result[FeedTemplate::TEMPLATE_CONFIG] = $this->baseSerializer->serialize($data);
        }

        return $result;
    }

    private function convertCsvFieldSerialization(string $csvField): string
    {
        try {
            $unserializedValue = $this->serializer->unserialize($csvField);
            $convertedValue = $this->baseSerializer->serialize($unserializedValue);

            return $convertedValue !== false ? $convertedValue : $csvField;
        } catch (\Exception $e) {
            return $csvField;
        }
    }
}
