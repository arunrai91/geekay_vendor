<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Plugin\Ui\Component\Filters\Type\Select;

use Amasty\Rolepermissions\Helper\Data;
use Magento\Store\Model\Store;
use Magento\Ui\Component\Filters\Type\Select;

class RestrictAllStores
{
    /**
     * @var Data
     */
    private $helper;

    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    public function beforePrepare(Select $subject): void
    {
        if ($subject->getName() === Store::STORE_ID) {
            $rule = $this->helper->currentRule();

            if ($rule && $rule->getScopeAccessMode()) {
                $config = $subject->getData('config');
                unset($config['caption']);
                $subject->setData('config', $config);
            }
        }
    }
}
