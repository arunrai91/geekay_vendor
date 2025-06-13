<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Api\Data;

use Magento\Framework\Exception\NoSuchEntityException;

interface OptionSettingRepositoryInterface
{
    public const TABLE = 'amasty_amshopby_option_setting';

    /**
     * @return OptionSettingInterface
     * @throws NoSuchEntityException
     */
    public function get($value, $field = null);

    /**
     * @param string $filterCode
     * @param int $optionId
     * @param int $storeId
     * @return OptionSettingInterface
     * @deprecared use getByCode instead
     */
    public function getByParams($filterCode, $optionId, $storeId);

    /**
     * @param string $attributeCode
     * @param int $optionId
     * @param int $storeId
     * @return OptionSettingInterface
     */
    public function getByCode(string $attributeCode, int $optionId, int $storeId): OptionSettingInterface;

    /**
     * @param OptionSettingInterface $optionSetting
     * @return OptionSettingRepositoryInterface
     */
    public function save(OptionSettingInterface $optionSetting);

    /**
     * @param int $storeId
     * @return array
     */
    public function getAllFeaturedOptionsArray($storeId);

    /**
     * @param int $optionId
     * @return void
     */
    public function deleteByOptionId(int $optionId);
}
