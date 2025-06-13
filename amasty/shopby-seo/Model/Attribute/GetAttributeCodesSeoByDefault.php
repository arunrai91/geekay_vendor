<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model\Attribute;

use Amasty\ShopbySeo\Model\ResourceModel\Attribute\LoadAttributeCodesSeoByDefault;

class GetAttributeCodesSeoByDefault
{
    /**
     * @var LoadAttributeCodesSeoByDefault
     */
    private $loadAttributeCodesSeoByDefault;

    /**
     * @var string[]
     */
    private $attributeCodesSeoByDefault;

    /**
     * @var string[]
     */
    private $attributesToExclude;

    public function __construct(
        LoadAttributeCodesSeoByDefault $loadAttributeCodesSeoByDefault,
        array $attributesToExclude = []
    ) {
        $this->loadAttributeCodesSeoByDefault = $loadAttributeCodesSeoByDefault;
        $this->attributesToExclude = array_values($attributesToExclude);
    }

    /**
     * @return string[]
     */
    public function execute(): array
    {
        if ($this->attributeCodesSeoByDefault === null) {
            $this->attributeCodesSeoByDefault = $this->loadAttributeCodesSeoByDefault->execute();
            $this->attributeCodesSeoByDefault = array_diff(
                $this->attributeCodesSeoByDefault,
                $this->attributesToExclude
            );
        }

        return $this->attributeCodesSeoByDefault;
    }
}
