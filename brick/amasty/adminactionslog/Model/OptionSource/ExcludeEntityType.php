<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

class ExcludeEntityType implements OptionSourceInterface
{
    /**
     * @var array[]
     */
    private $entityList;

    public function __construct(
        array $entityList = []
    ) {
        $this->entityList = $entityList;
    }

    public function toOptionArray(): array
    {
        $result = [];

        foreach ($this->toArray() as $value => $label) {
            $result[] = ['value' => $value, 'label' => $label];
        }

        return $result;
    }

    public function toArray(): array
    {
        return $this->entityList;
    }
}
