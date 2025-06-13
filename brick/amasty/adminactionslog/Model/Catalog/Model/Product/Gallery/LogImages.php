<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\Catalog\Model\Product\Gallery;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Api\Logging\MetadataInterfaceFactory;
use Amasty\AdminActionsLog\Logging\ActionFactory;
use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Framework\Exception\LocalizedException;

class LogImages
{
    /**
     * @var ActionFactory
     */
    private $actionFactory;

    /**
     * @var MetadataInterfaceFactory
     */
    private $metadataFactory;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var array ['SKU' => [imageId => \Magento\Catalog\Model\Product\Gallery\Entry]]
     */
    private $oldImages = [];

    /**
     * @var array ['SKU' => [imageId => \Magento\Catalog\Model\Product\Gallery\Entry]]
     */
    private $deletedImages = [];

    public function __construct(
        ActionFactory $actionFactory,
        MetadataInterfaceFactory $metadataFactory,
        EventManager $eventManager
    ) {
        $this->actionFactory = $actionFactory;
        $this->metadataFactory = $metadataFactory;
        $this->eventManager = $eventManager;
    }

    public function processGalleryBeforeSave(ProductInterface $product): void
    {
        if (!$this->validateLogging($product)) {
            return;
        }

        $productSku = $product->getSku();
        $this->oldImages[$productSku] = [];
        try {
            $productGallery = $product->getMediaGalleryEntries();
        } catch (LocalizedException $exception) {
            return;
        }

        $mediaGalleryData = $product->getData('media_gallery') ?? [];
        $removedImages = [];
        if ($mediaGalleryData && isset($mediaGalleryData['images'])) {
            $removedImages = array_filter(array_column($mediaGalleryData['images'], 'removed', 'value_id'));
        }

        $this->deletedImages[$productSku] = [];
        foreach ($productGallery as $image) {
            if (isset($removedImages[$image['id']])) {
                $this->deletedImages[$productSku][$image['id']] = $image;
            } elseif (!empty($image['id'])) {
                $this->oldImages[$productSku][$image['id']] = $image;
            }
        }

        foreach ($this->oldImages[$productSku] as $image) {
            $this->executeLoggingAction($image, MetadataInterface::EVENT_SAVE_BEFORE);
        }

        foreach ($this->deletedImages[$productSku] as $image) {
            $this->eventManager->dispatch('model_delete_before', ['object' => $image]);
        }
    }

    public function processGalleryAfterSave(ProductInterface $product): void
    {
        if (!$this->validateLogging($product)) {
            return;
        }

        $gallery = [];
        $productSku = $product->getSku();
        $productGallery = $product->getMediaGalleryEntries() ?? [];
        foreach ($productGallery as $image) {
            if (!isset($this->deletedImages[$productSku][$image['id']])) {
                if (!isset($this->oldImages[$productSku][$image['id']])) {
                    $image->isObjectNew(true);
                    $gallery[$image['id']] = $image;
                } else {
                    $oldImage = $this->oldImages[$productSku][$image['id']];
                    $oldImage->setData($image->getData());
                    $gallery[$image['id']] = $oldImage;
                }
            }
        }

        foreach ($gallery as $image) {
            $this->executeLoggingAction($image, MetadataInterface::EVENT_SAVE_AFTER);
        }

        foreach ($this->deletedImages[$productSku] as $image) {
            $this->eventManager->dispatch('model_delete_after', ['object' => $image]);
        }
    }

    private function validateLogging(ProductInterface $product): bool
    {
        $isValid = true;
        $existedImages = $product->getMediaGalleryImages()->getItems();
        $mediaGalleryData = $product->getMediaGallery('images') ?? [];
        if (count($existedImages) === count($mediaGalleryData) && !array_diff_key($mediaGalleryData, $existedImages)) {
            $isValid = false;
        }

        return $isValid;
    }

    private function executeLoggingAction(
        ProductAttributeMediaGalleryEntryInterface $loggingObject,
        string $eventName
    ): void {
        $metadata = $this->metadataFactory->create([
            'eventName' => $eventName,
            'loggingObject' => $loggingObject
        ]);
        $actionHandler = $this->actionFactory->create($metadata);
        $actionHandler->execute();
    }
}
