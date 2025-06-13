<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Plugin\Store\Model;

use Amasty\Rolepermissions\Helper\Data;
use Amasty\Rolepermissions\Model\Rule;
use Magento\Framework\Registry;

class WebsiteRepository
{
    public const AM_USE_ALL_WEBSITES = 'am_use_all_websites';

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Registry
     */
    private $coreRegistry;

    public function __construct(
        Data $helper,
        Registry $coreRegistry
    ) {
        $this->helper = $helper;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * @param \Magento\Store\Model\WebsiteRepository $subject
     * @param \Magento\Store\Api\Data\WebsiteInterface[]  $result
     *
     * @return array
     */
    public function afterGetList(
        \Magento\Store\Model\WebsiteRepository $subject,
        $result
    ) {
        if (!$this->coreRegistry->registry(self::AM_USE_ALL_WEBSITES)) {
            $rule = $this->helper->currentRule();

            if ($rule && ($rule->getScopeWebsites() || $rule->getScopeStoreviews())) {
                foreach ($result as $key => $website) {
                    $websiteId = $website->getId();
                    $accessible = in_array($websiteId, $rule->getPartiallyAccessibleWebsites());

                    if (!$accessible && $websiteId != 0) {
                        unset($result[$key]);
                    }
                }
            }
        }

        return $result;
    }
}
