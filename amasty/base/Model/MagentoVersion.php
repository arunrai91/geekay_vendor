<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Model;

use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Class MagentoVersion is used for faster retrieving magento version
 * and corresponding version of magento by Mage-OS version.
 */
class MagentoVersion
{
    public const MAGENTO_VERSION = 'amasty_magento_version';
    public const MAGE_OS_PRODUCT_NAME = 'Mage-OS';
    public const DEFAULT_MAGENTO_VERSION = '2.4.7';

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var Config
     */
    private $cache;

    /**
     * @var string
     */
    private $magentoVersion;

    /**
     * @var array
     */
    private $mageOsVersionMap;

    public function __construct(
        Config $cache,
        ProductMetadataInterface $productMetadata,
        array $mageOsVersionMap = []
    ) {
        $this->productMetadata = $productMetadata;
        $this->cache = $cache;
        $this->mageOsVersionMap = $mageOsVersionMap;
    }

    /**
     * @return string
     */
    public function get()
    {
        if (!$this->magentoVersion
            && !($this->magentoVersion = $this->cache->load(self::MAGENTO_VERSION))
        ) {
            $this->magentoVersion = $this->getVersion();
            $this->cache->save($this->magentoVersion, self::MAGENTO_VERSION);
        }

        return $this->magentoVersion;
    }

    private function getVersion(): string
    {
        if ($this->productMetadata->getName() === self::MAGE_OS_PRODUCT_NAME) {
            return $this->mageOsVersionMap[$this->productMetadata->getVersion()] ?? self::DEFAULT_MAGENTO_VERSION;
        }

        return $this->productMetadata->getVersion();
    }
}
