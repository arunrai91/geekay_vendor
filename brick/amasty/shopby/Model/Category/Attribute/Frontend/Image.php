<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Category\Attribute\Frontend;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\UrlInterface;
use Magento\Framework\Image\AdapterFactory as ImageAdapterFactory;

class Image
{
    public const IMAGE_RESIZER_DIR = 'images';
    public const IMAGE_RESIZER_CACHE_DIR = self::IMAGE_RESIZER_DIR . DIRECTORY_SEPARATOR . DirectoryList::CACHE;

    /**
     * @var ImageAdapterFactory
     */
    private ImageAdapterFactory $imageAdapterFactory;

    /**
     * @var string
     */
    private $relativeFilename;

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * @var WriteInterface
     */
    private WriteInterface $fileSystem;

    /**
     * @var Repository
     */
    private Repository $assetRepo;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var string
     */
    private string $placeholder;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var File
     */
    private File $fileIo;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        Filesystem $fileSystem,
        Repository $repository,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        File $fileIo,
        ImageAdapterFactory $adapterFactory,
        ScopeConfigInterface $scopeConfig,
        $placeholder = 'Amasty_Shopby::images/category/placeholder/placeholder.jpg'
    ) {
        $this->storeManager = $storeManager;
        $this->fileSystem = $fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->assetRepo = $repository;
        $this->placeholder = $placeholder;
        $this->logger = $logger;
        $this->fileIo = $fileIo;
        $this->imageAdapterFactory = $adapterFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param string $imageName
     * @param bool $withPlaceholder
     * @param null|int $width
     * @param null|int $height
     * @return bool|null|string
     */
    public function getImageUrl($imageName, $withPlaceholder = false, $width = null, $height = null)
    {
        if ($this->fileSystem->isFile('catalog/category/' . $imageName)) {
            return $this->resizeAndGetUrl($this->buildUrl($imageName), $width, $height);
        } else {
            if ($withPlaceholder) {
                $placeholderUploaded = $this->getThumbnailPlaceholder();
                if ($placeholderUploaded
                    && $this->fileSystem->isFile('catalog/category/' . $placeholderUploaded)
                ) {
                    return $this->buildUrl($placeholderUploaded);
                }
                return $this->assetRepo->getUrl($this->placeholder);
            }
        }

        return null;
    }

    private function getThumbnailPlaceholder()
    {
        return $this->scopeConfig->getValue('catalog/category_placeholder/thumbnail');
    }

    /**
     * @param string $imageName
     * @return string
     */
    private function buildUrl($imageName)
    {
        return $this->storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        ) . 'catalog/category/' . $imageName;
    }

    /**
     * @param string $imageUrl
     * @param int $width
     * @param int $height
     * @return bool|string
     */
    private function resizeAndGetUrl($imageUrl, $width = null, $height = null)
    {
        $resultUrl = $imageUrl;
        $this->initRelativeFilenameFromUrl($imageUrl);
        if (!$this->relativeFilename) {
            return $resultUrl;
        }
        $this->initSize($width, $height);

        try {
            $resizedUrl = $this->getResizedImageUrl();
            if (!$resizedUrl) {
                if ($this->resizeAndSaveImage()) {
                    $resizedUrl = $this->getResizedImageUrl();
                }
            }
            if ($resizedUrl) {
                $resultUrl = $resizedUrl;
            }
        } catch (\Exception $e) {
            $this->logger->error("Amasty Shopby: Resize Image Error: " . $e->getMessage());
        }
        return $resultUrl;
    }

    /**
     * @param string $imageUrl
     * @return bool|mixed|string
     */
    private function initRelativeFilenameFromUrl($imageUrl)
    {
        $this->relativeFilename = false;
        $storeUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        if (false !== strpos($imageUrl, $storeUrl)) {
            $relativeFilename = str_replace($storeUrl, '', $imageUrl);
            $this->relativeFilename = $relativeFilename;
        }
        return $this->relativeFilename;
    }

    /**
     * Init resize dimensions
     *
     * @param int $width
     * @param int $height
     * @return void
     */
    private function initSize($width, $height)
    {
        $this->width = $width;
        $this->height = ($height === null ? $width : $height);
    }

    /**
     * Resize and save new generated image
     *
     * @return bool
     */
    private function resizeAndSaveImage()
    {
        if (!$this->width || !$this->height) {
            return false;
        }
        if (!$this->fileSystem->isFile($this->relativeFilename)) {
            return false;
        }
        $imageAdapter = $this->imageAdapterFactory->create();
        $imageAdapter->open($this->getAbsolutePathOriginal());
        $imageAdapter->constrainOnly(false);
        $imageAdapter->keepTransparency(true);
        $imageAdapter->keepFrame(true);
        $imageAdapter->keepAspectRatio(true);
        $imageAdapter->backgroundColor([255, 255, 255]);
        $imageAdapter->resize($this->width, $this->height);
        $imageAdapter->save($this->getAbsolutePathResized());
        return true;
    }

    /**
     * Get url of resized image
     *
     * @return bool|string
     */
    private function getResizedImageUrl()
    {
        $relativePath = $this->getRelativePathResizedImage();
        if ($this->fileSystem->isFile($relativePath)) {
            return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $relativePath;
        }
        return false;
    }

    /**
     * Get absolute path from resized image
     *
     * @return string
     */
    private function getAbsolutePathResized()
    {
        return $this->fileSystem->getAbsolutePath($this->getRelativePathResizedImage());
    }

    /**
     * Get absolute path from original image
     *
     * @return string
     */
    private function getAbsolutePathOriginal()
    {
        return $this->fileSystem->getAbsolutePath($this->relativeFilename);
    }

    /**
     * Get relative path where the resized image is saved
     *
     * In order to have unique paths, we use the original image path plus the ResizeSubFolderName.
     *
     * @return string
     */
    private function getRelativePathResizedImage()
    {
        $pathInfo = $this->fileIo->getPathInfo($this->relativeFilename);
        if (!isset($pathInfo['basename']) || !isset($pathInfo['dirname'])) {
            return false;
        }
        $relativePathParts = [
            self::IMAGE_RESIZER_CACHE_DIR,
            $pathInfo['dirname'],
            $this->getResizeSubFolderName(),
            $pathInfo['basename'],
        ];
        return implode(DIRECTORY_SEPARATOR, $relativePathParts);
    }

    /**
     * Get sub folder name where the resized image will be saved
     *
     * @return string
     */
    private function getResizeSubFolderName()
    {
        $subPath = $this->width . "x" . $this->height;
        return $subPath;
    }
}
