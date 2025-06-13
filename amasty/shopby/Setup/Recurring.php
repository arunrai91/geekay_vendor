<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Setup;

use Amasty\Base\Model\MagentoVersion;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class Recurring implements InstallSchemaInterface
{
    public const FOLDER_INVALID = 'code/Amasty/Shopby/view/frontend/web/css/';
    public const FOLDER_REBUILD = 'app/code/Amasty/Shopby';

    /**
     * @var MagentoVersion
     */
    private $magentoVersion;

    /**
     * @var ConfigInterface
     */
    private $resourceConfig;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(
        MagentoVersion $magentoVersion,
        ConfigInterface $resourceConfig,
        Filesystem $filesystem
    ) {
        $this->magentoVersion = $magentoVersion;
        $this->resourceConfig = $resourceConfig;
        $this->filesystem = $filesystem;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws LocalizedException
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($this->magentoVersion->get(), '2.3.3', '<')) {
            throw new LocalizedException(
                __('Amasty Improved Layered Navigation supports Magento v.2.3.3+ only')
            );
        }

        $directory = $this->filesystem->getDirectoryRead(DirectoryList::APP);
        if ($directory->isExist(self::FOLDER_INVALID)) {
            throw new LocalizedException(
                __("\nWARNING: This update requires removing folder %1.\n"
                    . "Remove this folder and unpack new version of package into %1.\n"
                    . "Run `php bin/magento setup:upgrade` again", self::FOLDER_REBUILD)
            );
        }
    }
}
