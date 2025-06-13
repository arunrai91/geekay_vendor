<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Banners Lite for Magento 2 (System)
 */

namespace Amasty\BannersLite\Plugin\MediaGalleryCatalogIntegration\Plugin\SaveBaseCategoryImageInformation;

use Amasty\BannersLite\Model\BannerImageUpload;
use Magento\Catalog\Model\ImageUploader;
use Magento\MediaGalleryCatalogIntegration\Plugin\SaveBaseCategoryImageInformation;
use Magento\MediaGalleryUiApi\Api\ConfigInterface;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class FixFilesSynchronization
{
    /**
     * @var string
     */
    private string $originResult = '';

    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;

    public function __construct(
        ConfigInterface $config
    ) {
        $this->config = $config;
    }

    public function beforeAfterMoveFileFromTmp(
        SaveBaseCategoryImageInformation $subject,
        ImageUploader $imageUploader,
        string $imagePath,
        string $initialImageName
    ): array {
        if (!$imageUploader instanceof BannerImageUpload
            || !$this->config->isEnabled()
            // $returnRelativePath = true
            || str_contains($imagePath, $imageUploader->getBasePath())
        ) {
            return [$imageUploader, $imagePath, $initialImageName];
        }

        $this->originResult = $imagePath;
        $imagePath = $imageUploader->getBasePath() . '/' . $imagePath;

        return [$imageUploader, $imagePath, $initialImageName];
    }

    public function afterAfterMoveFileFromTmp(
        SaveBaseCategoryImageInformation $subject,
        string $result,
        ImageUploader $imageUploader,
        string $imagePath,
        string $initialImageName
    ): string {
        if ($imageUploader instanceof BannerImageUpload
            && $this->originResult
        ) {
            return $this->originResult;
        }

        return $result;
    }

    public function _resetState(): void
    {
        $this->originResult = '';
    }
}
