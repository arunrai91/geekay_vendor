<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Plugin\Eav\Model\Entity;

use Amasty\Rolepermissions\Helper\Data;
use Magento\Eav\Model\Entity\EntityInterface;
use Magento\Framework\App\RequestInterface;

class AbstractEntity
{
    /**
     * @var Data $helper
     */
    private $helper;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var array
     */
    private $actionNamesToIgnoreRestrictAttributes;

    public function __construct(
        Data $helper,
        RequestInterface $request,
        array $actionNamesToIgnoreRestrictAttributes = []
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->actionNamesToIgnoreRestrictAttributes = $actionNamesToIgnoreRestrictAttributes;
    }

    /**
     * @param EntityInterface $subject
     * @param array $result
     * @return array
     */
    public function afterGetAttributesByCode($subject, array $result): array
    {
        $currentRule = $this->helper->currentRule();

        if ($currentRule
            && $currentRule->getAttributes()
            && !in_array($this->request->getFullActionName(), $this->actionNamesToIgnoreRestrictAttributes)
        ) {
            $allowedCodes = $this->helper->getAllowedAttributeCodes();

            if (is_array($allowedCodes)) {
                foreach ($result as $key => $value) {
                    if (!in_array($key, $allowedCodes)) {
                        unset($result[$key]);
                    }
                }
            }
        }

        return $result;
    }
}
