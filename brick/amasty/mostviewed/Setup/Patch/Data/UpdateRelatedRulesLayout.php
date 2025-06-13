<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Setup\Patch\Data;

use Amasty\Mostviewed\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Area as Area;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateRelatedRulesLayout implements DataPatchInterface
{
    /**
     * @var AppState
     */
    private $appState;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    public function __construct(
        AppState $appState,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        GroupRepositoryInterface $groupRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->groupRepository = $groupRepository;
        $this->appState = $appState;
    }

    /**
     * @return string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return UpdateRelatedRulesLayout
     */
    public function apply()
    {
        $this->appState->emulateAreaCode(Area::AREA_ADMINHTML, [$this, 'resaveRules']);

        return $this;
    }

    /**
     * Trigger re-save rules for updating block name into layout_update.
     * @see \Amasty\Mostviewed\Model\Layout\Updater::generateLayoutUpdateXml
     */
    public function resaveRules(): void
    {
        foreach ($this->groupRepository->getList($this->searchCriteriaBuilder->create())->getItems() as $group) {
            $group->setHasDataChanges(true);
            $this->groupRepository->save($group);
        }
    }
}
