<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Catalog\Model;

use Magento\Catalog\Model\ImageUploader;

class ImageUploaderPlugin
{
    public function beforeMoveFileFromTmp(ImageUploader $subject, $path, $returnRelativePath = false): array
    {
        $posLastSlash = strripos($path, '/');
        $path = $posLastSlash && strpos($path, '/category/') !== false ? substr($path, $posLastSlash + 1) : $path;

        return [$path, $returnRelativePath];
    }
}
