<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Banners Lite for Magento 2 (System)
 */

namespace Amasty\BannersLite\Api\Data;

/**
 * @api
 */
interface FileContentInterface
{
    public const BASE64_ENCODED_DATA = 'base64_encoded_data';
    public const NAME = 'name_with_extension';

    /**
     * @return string
     */
    public function getBase64EncodedData(): string;

    /**
     * @param string $base64EncodedData
     *
     * @return void
     */
    public function setBase64EncodedData(string $base64EncodedData): void;

    /**
     * @return string
     */
    public function getNameWithExtension(): string;

    /**
     * @param string $nameWithExtension
     *
     * @return void
     */
    public function setNameWithExtension(string $nameWithExtension): void;
}
