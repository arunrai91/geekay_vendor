<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\DiffFinder;

use FineDiff\Diff;
use Magento\Framework\ObjectManagerInterface;

class FineDiffAdapter implements DiffFinderAdapterInterface
{
    /**
     * @var Diff|null
     */
    private $diffFinder;

    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        //ToDo: Remove after updating library "d4h/finediff"
        $currentErrorLevel = error_reporting();
        error_reporting($currentErrorLevel & ~E_DEPRECATED);
        if (class_exists(Diff::class)) {
            $this->diffFinder = $objectManager->create(Diff::class);
        }
        error_reporting($currentErrorLevel);
    }

    public function render(string $fromText, string $toText): string
    {
        if ($this->diffFinder === null) {
            throw new \RuntimeException(
                '\'d4h/finediff\' library not found. '
                . 'Please run \'composer require d4h/finediff\' command to install it.'
            );
        }

        return $this->diffFinder->render($fromText, $toText);
    }
}
