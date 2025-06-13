<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Page for Magento 2 (System)
 */

namespace Amasty\ShopbyPage\Model\Page;

use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class ImagesManager
{
    public const IMAGES_DIR = '/amasty/shopby/page_images/';

    /**
     * @var Filesystem
     */
    private Filesystem $fileSystem;

    /**
     * @var FileDriver
     */
    private FileDriver $fileDriver;

    /**
     * @var UploaderFactory
     */
    private UploaderFactory $uploaderFactory;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    public function __construct(
        Filesystem $fileSystem,
        FileDriver $fileDriver,
        UploaderFactory $uploaderFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->fileSystem = $fileSystem;
        $this->fileDriver = $fileDriver;
        $this->uploaderFactory = $uploaderFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @throws FileSystemException
     * @throws Exception
     */
    public function uploadImage(string $fileId): string
    {
        $mediaDir = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setFilesDispersion(false);
        $uploader->setFilenamesCaseSensitivity(false);
        $uploader->setAllowRenameFiles(true);
        $uploader->setAllowedExtensions(['jpg', 'png', 'jpeg', 'gif', 'bmp', 'svg']);
        $uploader->save($mediaDir->getAbsolutePath(static::IMAGES_DIR));

        return $uploader->getUploadedFileName();
    }

    /**
     * @throws FileSystemException
     */
    public function removeImage(string $image): void
    {
        $path = $this->getImagePath($image);
        if ($this->fileDriver->isExists($path)) {
            $this->fileDriver->deleteFile($path);
        }
    }

    public function getImagePath(string $image): string
    {
        $mediaDir = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
        $imgPath = self::IMAGES_DIR . $image;

        return $mediaDir->getAbsolutePath($imgPath);
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getImageUrl(string $image, ?int $storeId = null): string
    {
        $baseUrl = $this->storeManager->getStore($storeId)->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        return rtrim($baseUrl, '/') . self::IMAGES_DIR . $image;
    }
}
