<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Model\Config\BackendModel;

use Amasty\Geoip\Model\Source\RefreshIpBehaviour;
use Amasty\Geoip\Model\SyncService\LicenseValidatorClient;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class RefreshIpDatabase extends Value
{
    /**
     * @var LicenseValidatorClient
     */
    private $licenseValidatorClient;

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        LicenseValidatorClient $licenseValidatorClient,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->licenseValidatorClient = $licenseValidatorClient;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @throws LocalizedException
     */
    public function beforeSave(): self
    {
        if ((int)$this->getValue() === RefreshIpBehaviour::VIA_AMASTY_SERVICE
            && !$this->licenseValidatorClient->isValid()
        ) {
            throw new LocalizedException($this->licenseValidatorClient->getMessage());
        }

        return parent::beforeSave();
    }
}
