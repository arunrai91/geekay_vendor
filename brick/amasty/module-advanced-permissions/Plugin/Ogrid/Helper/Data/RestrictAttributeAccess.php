<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Plugin\Ogrid\Helper\Data;

use Amasty\Ogrid\Helper\Data as OgridHelper;
use Amasty\Ogrid\Model\ResourceModel\Attribute\Collection as AttributeCollection;
use Amasty\Rolepermissions\Helper\Data as Helper;
use Amasty\Rolepermissions\Utils\MainAliasResolver;
use Magento\Eav\Api\Data\AttributeInterface;

class RestrictAttributeAccess
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var MainAliasResolver
     */
    private $mainAliasResolver;

    /**
     * @var bool
     */
    private $processed = false;

    public function __construct(
        Helper $helper,
        MainAliasResolver $mainAliasResolver
    ) {
        $this->helper = $helper;
        $this->mainAliasResolver = $mainAliasResolver;
    }

    public function afterGetAttributeCollection(
        OgridHelper $subject,
        AttributeCollection $attributeCollection
    ): AttributeCollection { // @phpstan-ignore class.notFound
        if ($this->processed) {
            return $attributeCollection;
        }

        if ($restrictedAttributeIds = $this->helper->getRestrictedAttributeIds()) {
            $mainAlias = '';

            if ($alias = $this->mainAliasResolver->resolve($attributeCollection->getSelect())) {
                $mainAlias = sprintf('%s.', $alias);
            }

            $attributeCollection->addFieldToFilter(
                $mainAlias . AttributeInterface::ATTRIBUTE_ID,
                ['nin' => $restrictedAttributeIds]
            );
        }

        $this->processed = true;

        return $attributeCollection;
    }
}
