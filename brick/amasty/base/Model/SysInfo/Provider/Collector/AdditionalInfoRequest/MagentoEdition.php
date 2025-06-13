<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Model\SysInfo\Provider\Collector\AdditionalInfoRequest;

use Amasty\Base\Model\SysInfo\Provider\Collector\CollectorInterface;
use Magento\Framework\App\ProductMetadataInterface;

class MagentoEdition implements CollectorInterface
{
    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    public function __construct(
        ProductMetadataInterface $productMetadata
    ) {
        $this->productMetadata = $productMetadata;
    }

    public function get()
    {
        return $this->productMetadata->getEdition();
    }
}
