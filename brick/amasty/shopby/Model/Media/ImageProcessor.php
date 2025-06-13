<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Media;

use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Model\ImageUploader;

class ImageProcessor extends ImageUploader
{
    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var AdapterFactory
     */
    private AdapterFactory $imageFactory;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Filesystem $filesystem,
        AdapterFactory $imageFactory,
        Database $coreFileStorageDatabase,
        UploaderFactory $uploaderFactory,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        $baseTmpPath,
        $basePath,
        array $allowedExtensions = []
    ) {
        $this->filesystem = $filesystem;
        $this->imageFactory = $imageFactory;
        parent::__construct(
            $coreFileStorageDatabase,
            $filesystem,
            $uploaderFactory,
            $storeManager,
            $logger,
            $baseTmpPath,
            $basePath,
            $allowedExtensions
        );
    }

    /**
     * @param string $image
     * @return void
     */
    public function resize($image)
    {
        $baseTmpPath = $this->getBaseTmpPath();
        $baseTmpImagePath = $this->getFilePath($baseTmpPath, $image);

        $absolutePath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)
            ->getAbsolutePath($baseTmpImagePath);

        $imageResize = $this->imageFactory->create();
        $imageResize->open($absolutePath);
        $imageResize->constrainOnly(false);
        $imageResize->keepTransparency(true);
        $imageResize->keepFrame(false);
        $imageResize->keepAspectRatio(true);
        $imageResize->save($absolutePath);
    }
}
