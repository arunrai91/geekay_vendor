<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Exceptions;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class LockProcessException extends LocalizedException
{
    public function __construct(?Phrase $phrase = null, ?Exception $cause = null, $code = 0)
    {
        if (!$phrase) {
            $phrase = __('Feed generation is currently unavailable as the indexing process is in progress. '
            . 'Please wait until indexing is complete before trying again');
        }
        parent::__construct($phrase, $cause, (int) $code);
    }
}
