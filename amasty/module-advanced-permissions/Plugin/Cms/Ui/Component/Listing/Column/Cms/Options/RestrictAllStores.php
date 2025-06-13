<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Plugin\Cms\Ui\Component\Listing\Column\Cms\Options;

use Amasty\Rolepermissions\Helper\Data;
use Magento\Cms\Ui\Component\Listing\Column\Cms\Options;

class RestrictAllStores
{
    public const ALL_STORE_VIEWS_KEY = 0;

    /**
     * @var Data
     */
    private $helper;

    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    public function afterToOptionArray(Options $subject, array $result)
    {
        if ($rule = $this->helper->currentRule()) {
            $allowedStores = $rule->getScopeStoreviews();

            if ($allowedStores) {
                unset($result[self::ALL_STORE_VIEWS_KEY]);
            }
        }

        return $result;
    }
}
