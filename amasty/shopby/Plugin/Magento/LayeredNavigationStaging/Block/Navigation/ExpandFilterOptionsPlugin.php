<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Magento\LayeredNavigationStaging\Block\Navigation;

use Amasty\Shopby\Model\Layer\GetFiltersExpanded;
use Magento\LayeredNavigationStaging\Block\Navigation;

class ExpandFilterOptionsPlugin
{
    public const SEARCH = 'data-role="collapsible"';
    public const REPLACE = 'data-role="collapsible" data-collapsible="true"';

    /**
     * @var GetFiltersExpanded
     */
    private $getFiltersExpanded;

    public function __construct(
        GetFiltersExpanded $getFiltersExpanded
    ) {
        $this->getFiltersExpanded = $getFiltersExpanded;
    }

    public function afterToHtml(
        Navigation $subject, // @phpstan-ignore class.notFound
        string $html
    ): string {
        $expanded = $this->getFiltersExpanded->execute($subject->getFilters());
        $search = self::SEARCH;
        $replace = self::REPLACE;
        $counter = 0;
        $html = preg_replace_callback(
            sprintf('/%s/', $search),
            function () use ($search, $replace, &$counter, $expanded) {
                return in_array($counter++, $expanded) ? $replace : $search;
            },
            $html
        );

        return $html;
    }
}
