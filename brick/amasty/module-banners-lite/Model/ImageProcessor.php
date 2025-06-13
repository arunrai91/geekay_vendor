<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Banners Lite for Magento 2 (System)
 */

namespace Amasty\BannersLite\Model;

use Amasty\Base\Model\Serializer;
use Amasty\BannersLite\Model\BannerImageUpload;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class ImageProcessor
{
    /**
     * Banners area inside media folder
     */
    public const BANNERS_MEDIA_PATH = 'amasty/banners_lite';

    /**
     * Banners temporary area inside media folder
     */
    public const BANNERS_MEDIA_TMP_PATH = 'amasty/banners_lite/tmp';

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var BannerImageUpload
     */
    private $imageUploader;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Filesystem $filesystem,
        Serializer $serializer, //@deprecated backward compatibility
        BannerImageUpload $imageUploader,
        StoreManagerInterface $storeManager
    ) {
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->imageUploader = $imageUploader;
        $this->storeManager = $storeManager;
    }

    public function getBannerImageUrl(string $imageName): string
    {
        return rtrim($this->getBannerMedia($imageName), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $imageName;
    }

    public function moveFileFromTmp(string $imageName): string
    {
        return $this->imageUploader->moveFileFromTmp($imageName);
    }

    public function copyFile(string $imageName): string
    {
        try {
            return $this->imageUploader->duplicateFile($imageName);
        } catch (LocalizedException $exception) {
            // file already was duplicated
            return $imageName;
        }
    }

    public function deleteImage(string $bannerImage): void
    {
        if ($bannerImage) {
            $this->mediaDirectory->delete(
                $this->getBannersRelativePath($bannerImage)
            );
        }
    }

    private function getBannersRelativePath(string $bannerName): string
    {
        return self::BANNERS_MEDIA_PATH . DIRECTORY_SEPARATOR . $bannerName;
    }

    /**
     * Url type http://url/pub/media/amasty/banners_lite
     */
    private function getBannerMedia(string $imageName): string
    {
        $bannerMedia = $this->storeManager
            ->getStore()
            ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        if (strpos($imageName, self::BANNERS_MEDIA_PATH) === false) {
            $bannerMedia .= self::BANNERS_MEDIA_PATH;
        }

        return $bannerMedia;
    }
}
