<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Banners Lite for Magento 2 (System)
 */

namespace Amasty\BannersLite\Api;

use Amasty\BannersLite\Api\Data\FileContentInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * @api
 */
interface UploadFileInterface
{
    /**
     * @param FileContentInterface $fileContent
     *
     * @return string tmp file name
     * @throws LocalizedException
     */
    public function upload(FileContentInterface $fileContent): string;
}
