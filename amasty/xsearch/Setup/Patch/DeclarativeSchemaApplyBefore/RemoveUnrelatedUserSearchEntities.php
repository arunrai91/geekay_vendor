<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Setup\Patch\DeclarativeSchemaApplyBefore;

use Amasty\Xsearch\Model\ResourceModel\UserSearch;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class RemoveUnrelatedUserSearchEntities implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): self
    {
        $userSearchTable = $this->moduleDataSetup->getTable(UserSearch::MAIN_TABLE);

        if ($this->moduleDataSetup->tableExists($userSearchTable)) {
            $searchQueryTable = $this->moduleDataSetup->getTable('search_query');

            $connection = $this->moduleDataSetup->getConnection();
            $select = $connection->select();
            $select->from($searchQueryTable, 'query_id');

            $connection->delete($userSearchTable, ["query_id NOT IN (?)" => $select]);
        }

        return $this;
    }
}
