<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Module\ResourceInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class InstallAttributes implements DataPatchInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var ResourceInterface
     */
    private $moduleResource;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ResourceInterface $moduleResource,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->moduleResource = $moduleResource;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply()
    {
        $setupDataVersion = (string)$this->moduleResource->getDataVersion('Amasty_Rolepermissions');
        // Check if module was already installed or not.
        // If setup_version present in DB then we don't need to install fixtures, because setup_version is a marker.
        if (!$setupDataVersion) {
            /** @var EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'amrolepermissions_owner',
                [
                    'type'       => 'int',
                    'label'      => 'Product Owner',
                    'input'      => 'text',
                    'required'   => false,
                    'sort_order' => 10000,
                    'global'     => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'group'      => 'Product Details',
                    'visible'    => true,
                ]
            );
        }

        return $this;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
