<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Banners Lite for Magento 2 (System)
 */

namespace Amasty\BannersLite\Model\Webapi;

use Amasty\BannersLite\Api\Data\FileContentInterface;
use Amasty\BannersLite\Api\UploadFileInterface;
use Amasty\BannersLite\Model\ImageProcessor;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Uploader;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class UploadFile implements UploadFileInterface
{
    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    public function __construct(
        Filesystem $filesystem
    ) {
        $this->mediaDirectory = $filesystem->getDirectoryWrite(
            DirectoryList::MEDIA
        );
    }

    public function upload(FileContentInterface $fileContent): string
    {
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        if (!($content = base64_decode($fileContent->getBase64EncodedData()))) {
            throw new LocalizedException(__('Base64 Decode File Error'));
        }
        $absolutePath = $this->mediaDirectory->getAbsolutePath(ImageProcessor::BANNERS_MEDIA_TMP_PATH);
        $name = $fileContent->getNameWithExtension();

        if (!$this->mediaDirectory->isExist(ImageProcessor::BANNERS_MEDIA_TMP_PATH)) {
            $this->mediaDirectory->create(ImageProcessor::BANNERS_MEDIA_TMP_PATH);
        }

        $filePath = ImageProcessor::BANNERS_MEDIA_TMP_PATH . DIRECTORY_SEPARATOR . $name;
        if ($this->mediaDirectory->isExist($filePath)) {
            $name = Uploader::getNewFileName(
                $this->mediaDirectory->getAbsolutePath($absolutePath . DIRECTORY_SEPARATOR . $name)
            );
        }

        $this->mediaDirectory->getDriver()->filePutContents(
            $absolutePath . DIRECTORY_SEPARATOR . $name,
            $content
        );

        return $name;
    }
}
