<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Model\LicenceService\Api;

use Amasty\Base\Model\LicenceService\Api\Client\AdditionalInfo;
use Amasty\Base\Model\SimpleDataObject;

class RequestFacade
{
    /**
     * @var AdditionalInfo
     */
    private $additionalInfo;

    public function __construct(
        AdditionalInfo $additionalInfo
    ) {
        $this->additionalInfo = $additionalInfo;
    }

    public function getAdditionalInfo(array $params): SimpleDataObject
    {
        return $this->additionalInfo->requestAdditionalInfo($params);
    }
}
