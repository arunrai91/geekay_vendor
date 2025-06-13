<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Banners Lite for Magento 2 (System)
 */

namespace Amasty\BannersLite\Model;

use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\File\Uploader;

class BannerImageUpload extends ImageUploader
{
    public function moveFileFromTmp($imageName, $returnRelativePath = false): string
    {
        $baseTmpPath = $this->getBaseTmpPath();
        $basePath = $this->getBasePath();
        $validName = $this->getValidNewFileName($basePath, $imageName);

        $baseImagePath = $this->getFilePath($basePath, $validName);
        $baseTmpImagePath = $this->getFilePath($baseTmpPath, $imageName);

        try {
            $this->coreFileStorageDatabase->copyFile(
                $baseTmpImagePath,
                $baseImagePath
            );
            $this->mediaDirectory->renameFile(
                $baseTmpImagePath,
                $baseImagePath
            );
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while saving the file(s).')
            );
        }

        return $returnRelativePath ? $baseImagePath : $validName;
    }

    public function deleteFromTmp(string $fileName): void
    {
        $path = $this->getBaseTmpPath();
        $baseTmpImagePath = $this->getFilePath($path, $fileName);

        if ($this->mediaDirectory->isExist($baseTmpImagePath)) {
            $this->mediaDirectory->delete($baseTmpImagePath);
        }
    }

    public function duplicateFile(string $fileName): string
    {
        $basePath = $this->getBasePath();
        $validName = $this->getValidNewFileName($basePath, $fileName);

        $oldName = $this->getFilePath($basePath, $fileName);
        $newName = $this->getFilePath($basePath, $validName);

        $this->mediaDirectory->copyFile(
            $oldName,
            $newName
        );

        return $validName;
    }

    private function getValidNewFileName(string $basePath, string $imageName): string
    {
        $basePath = $this->mediaDirectory->getAbsolutePath($basePath) . DIRECTORY_SEPARATOR . $imageName;

        return Uploader::getNewFileName($basePath);
    }
}
