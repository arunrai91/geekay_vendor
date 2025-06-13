<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Page for Magento 2 (System)
 */

namespace Amasty\ShopbyPage\Model\Request\Page;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

class SelectionAttributeRegistry
{
    /**
     * @var AbstractAttribute|null
     */
    private ?AbstractAttribute $attribute = null;

    /**
     * @var int|null
     */
    private ?int $attributeIdx = null;

    public function setAttribute(?AbstractAttribute $attribute): void
    {
        $this->attribute = $attribute;
    }

    public function getAttribute(): ?AbstractAttribute
    {
        return $this->attribute;
    }

    public function setAttributeIdx(?int $attributeIdx): void
    {
        $this->attributeIdx = $attributeIdx;
    }

    public function getAttributeIdx(): ?int
    {
        return $this->attributeIdx;
    }

    public function _resetState(): void
    {
        $this->attribute = null;
        $this->attributeIdx = null;
    }
}
