<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Model\State;

class NewProductSavingFlag
{
    /**
     * @var bool
     */
    private $isSaving = false;

    public function isSaving(): bool
    {
        return $this->isSaving;
    }

    public function setIsSaving(bool $isSaving): void
    {
        $this->isSaving = $isSaving;
    }
}
