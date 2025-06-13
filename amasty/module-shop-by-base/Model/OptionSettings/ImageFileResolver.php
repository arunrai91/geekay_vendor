<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\OptionSettings;

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBase\Model\OptionSetting;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Store\Model\Store;

class ImageFileResolver
{
    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var DriverInterface
     */
    private $fileDriver;

    public function __construct(
        Filesystem $fileSystem,
        UploaderFactory $uploaderFactory,
        DriverInterface $fileDriver
    ) {
        $this->fileSystem = $fileSystem;
        $this->uploaderFactory = $uploaderFactory;
        $this->fileDriver = $fileDriver;
    }

    /**
     * @param OptionSettingInterface $optionSetting
     * @param int|string $fileId
     */
    public function resolveImageUpload(OptionSettingInterface $optionSetting, $fileId): string
    {
        $path = OptionSetting::IMAGES_DIR;
        $name = $this->uploadImage($fileId, $path);
        $this->resolveRemoveImage($optionSetting);

        return $name;
    }

    /**
     * @param OptionSettingInterface $optionSetting
     * @param int|string $fileId
     */
    public function resolveImageSliderUpload(OptionSettingInterface $optionSetting, $fileId): string
    {
        $path = OptionSetting::IMAGES_DIR . OptionSetting::SLIDER_DIR;
        $name = $this->uploadImage($fileId, $path);
        $this->resolveRemoveSliderImage($optionSetting);

        return $name;
    }

    /**
     * @param int|string $fileId
     * @param string $path
     */
    private function uploadImage($fileId, string $path): string
    {
        $mediaDir = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setFilesDispersion(false);
        $uploader->setFilenamesCaseSensitivity(false);
        $uploader->setAllowRenameFiles(true);
        $uploader->setAllowedExtensions(['jpg', 'png', 'jpeg', 'gif', 'bmp', 'svg']);
        $uploader->save($mediaDir->getAbsolutePath($path));

        return $uploader->getUploadedFileName();
    }

    public function resolveRemoveImage(OptionSettingInterface $optionSetting): void
    {
        if (!$optionSetting->getImage()) {
            return;
        }

        if ($optionSetting->getImageUseDefault() && (int) $optionSetting->getStoreId() !== Store::DEFAULT_STORE_ID) {
            return;
        }

        $path = $this->resolveImagePath($optionSetting);
        $this->deleteFile($path);
    }

    public function resolveRemoveSliderImage(OptionSettingInterface $optionSetting): void
    {
        if (!$optionSetting->getSliderImage()) {
            return;
        }

        if ($optionSetting->getSliderImageUseDefault()
            && (int) $optionSetting->getStoreId() !== Store::DEFAULT_STORE_ID
        ) {
            return;
        }

        $path = $this->resolveSliderImagePath($optionSetting);
        $this->deleteFile($path);
    }

    private function deleteFile(string $path): void
    {
        if ($this->fileDriver->isExists($path)) {
            $this->fileDriver->deleteFile($path);
        }
    }

    public function resolveImagePath(OptionSettingInterface $optionSetting): string
    {
        $imagePath = OptionSetting::IMAGES_DIR . $optionSetting->getImage();

        return $this->getMediaAbsolutePath($imagePath);
    }

    public function resolveSliderImagePath(OptionSettingInterface $optionSetting): string
    {
        $imagePath = OptionSetting::IMAGES_DIR . OptionSetting::SLIDER_DIR . $optionSetting->getSliderImage();

        return $this->getMediaAbsolutePath($imagePath);
    }

    public function getMediaAbsolutePath(string $imagePath): string
    {
        return $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($imagePath);
    }
}
