<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */
/**
 * Event observers configuration schema locator
 */
namespace Amasty\Base\Model\LessToCss\Config;

use Magento\Framework\Config\Dom\UrnResolver;

class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * @var UrnResolver
     */
    private $urnResolver;

    public function __construct(UrnResolver $urnResolver)
    {
        $this->urnResolver = $urnResolver;
    }

    /**
     * Get path to merged config schema
     *
     * @return string
     */
    public function getSchema()
    {
        return $this->urnResolver->getRealPath('urn:amasty:module:Amasty_Base:etc/less_to_css.xsd');
    }

    /**
     * Get path to pre file validation schema
     *
     * @return string
     */
    public function getPerFileSchema()
    {
        return $this->getSchema();
    }
}
