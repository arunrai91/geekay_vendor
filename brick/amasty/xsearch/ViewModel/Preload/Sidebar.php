<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\ViewModel\Preload;

use Amasty\Xsearch\Block\Search\AbstractSearch;
use Amasty\Xsearch\ViewModel\ConfigurableBlockRenderer;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Sidebar implements ArgumentInterface
{
    /**
     * @var ConfigurableBlockRenderer
     */
    private $blocksProvider;

    public function __construct(
        ConfigurableBlockRenderer $blocksProvider
    ) {
        $this->blocksProvider = $blocksProvider;
    }

    public function getSidebarBlocksHtml(): string
    {
        $result = '';

        /** @var AbstractSearch $block **/
        foreach ($this->blocksProvider->getBlocks() as $block) {
            $result .= $block->toHtml();
        }

        return $result;
    }
}
