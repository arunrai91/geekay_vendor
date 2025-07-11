<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Exceptions;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class WrongBehaviorInterface extends LocalizedException
{
    /**
     * @param ?Phrase $phrase
     * @param ?Exception $cause
     * @param int $code
     */
    public function __construct(?Phrase $phrase = null, ?Exception $cause = null, $code = null)
    {
        if (!$phrase) {
            $phrase = __('Wrong Behavior Interface.');
        }
        parent::__construct($phrase, $cause, (int) $code);
    }
}
