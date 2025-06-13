<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Exceptions;

use Magento\Framework\Exception\LocalizedException;

class LockProcessException extends LocalizedException
{
    public function __construct(\Magento\Framework\Phrase $phrase = null, \Exception $cause = null, $code = 0)
    {
        if (!$phrase) {
            $phrase = __('Feed generation is currently unavailable as the indexing process is in progress. '
            . 'Please wait until indexing is complete before trying again');
        }
        parent::__construct($phrase, $cause, (int) $code);
    }
}
