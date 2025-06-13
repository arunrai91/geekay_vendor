<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\ViewModel;

use Amasty\ShopbyBase\Model\OptionSetting;
use Magento\Framework\View\Element\Block\ArgumentInterface;

interface OptionProcessorInterface extends ArgumentInterface
{
    public const IMAGE_URL = 'image_url';

    public const LINK_URL = 'link_url';

    public const TITLE = 'title';

    public const SHORT_DESCRIPTION = 'short_description';

    public const TOOLTIP_JS = 'tooltip_js';

    public const DISPLAY_TITLE = 'display_title';

    /**
     * @param OptionSetting $optionSettings
     *
     * @return array
     */
    public function process(OptionSetting $optionSettings): array;
}
