<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\Indexer;

use Amasty\Xsearch\Model\Config;
use Amasty\Xsearch\Model\IsElasticEngine;
use Magento\CatalogSearch\Model\Indexer\IndexerHandlerFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\Indexer\DimensionProviderInterface;
use Magento\Store\Model\StoreDimensionProvider;
use Psr\Log\LoggerInterface;

class External implements ActionInterface
{
    /**
     * @var State
     */
    private $appState;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var IndexerHandlerFactory
     */
    private $indexerHandlerFactory;

    /**
     * @var array
     */
    private $data;

    /**
     * @var ExternalIndexerProvider
     */
    private $externalIndexerProvider;

    /**
     * @var DimensionProviderInterface
     */
    private $dimensionProvider;
    /**
     * @var IsElasticEngine|null
     */
    private $isElasticEngine;

    public function __construct(
        State $appState,
        ?Config $config, // @deprecated
        IndexerHandlerFactory $indexerHandlerFactory,
        ExternalIndexerProvider $externalIndexerProvider,
        DimensionProviderInterface $dimensionProvider,
        LoggerInterface $logger,
        array $data,
        IsElasticEngine $isElasticEngine = null
    ) {
        $this->appState = $appState;
        $this->logger = $logger;
        $this->indexerHandlerFactory = $indexerHandlerFactory;
        $this->data = $data;
        $this->externalIndexerProvider = $externalIndexerProvider;
        $this->dimensionProvider = $dimensionProvider;
        $this->isElasticEngine = $isElasticEngine ?? ObjectManager::getInstance()->get(IsElasticEngine::class);
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function executeFull()
    {
        if (!$this->checkCorrectAreaCode() || !$this->isElasticEngine->execute()) {
            return $this;
        }

        try {
            $saveHandler = $this->indexerHandlerFactory->create([
                'data' => $this->data
            ]);
            foreach ($this->dimensionProvider->getIterator() as $dimensions) {
                $storeId = $dimensions[StoreDimensionProvider::DIMENSION_NAME]->getValue();
                $saveHandler->cleanIndex($dimensions);
                $saveHandler->saveIndex($dimensions, $this->externalIndexerProvider->getDocuments((int)$storeId));
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function executeRow($id)
    {
        try {
            $this->executeFull();
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * @inheritdoc
     */
    public function executeList(array $ids)
    {
        try {
            $this->executeFull();
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    private function checkCorrectAreaCode(): bool
    {
        if ($this->appState->isAreaCodeEmulated()) {
            return $this->appState->getAreaCode() == \Magento\Framework\App\Area::AREA_FRONTEND;
        }

        return true;
    }
}
