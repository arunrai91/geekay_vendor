<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Banners Lite for Magento 2 (System)
 */

namespace Amasty\BannersLite\Model\Webapi;

use Amasty\BannersLite\Api\Data\FileContentInterface;
use Magento\Framework\DataObject;

class FileContent extends DataObject implements FileContentInterface
{
    public function getBase64EncodedData(): string
    {
        return (string)$this->_getData(FileContentInterface::BASE64_ENCODED_DATA);
    }

    public function setBase64EncodedData(string $base64EncodedData): void
    {
        $this->setData(FileContentInterface::BASE64_ENCODED_DATA, $base64EncodedData);
    }

    public function getNameWithExtension(): string
    {
        return (string)$this->_getData(FileContentInterface::NAME);
    }

    public function setNameWithExtension(string $nameWithExtension): void
    {
        $this->setData(FileContentInterface::NAME, $nameWithExtension);
    }
}
