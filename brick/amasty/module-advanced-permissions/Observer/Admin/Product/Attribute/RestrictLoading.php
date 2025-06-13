<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Observer\Admin\Product\Attribute;

use Amasty\Rolepermissions\Helper\Data as Helper;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class RestrictLoading implements ObserverInterface
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        Helper $helper,
        RequestInterface $request
    ) {
        $this->helper = $helper;
        $this->request = $request;
    }

    public function execute(Observer $observer): void
    {
        $restrictedAttributeIds = $this->helper->getRestrictedAttributeIds();
        $attributeId = $this->request->getParam(AttributeInterface::ATTRIBUTE_ID);
        if (!empty($restrictedAttributeIds)
            && $attributeId
            && in_array($attributeId, $restrictedAttributeIds)
        ) {
            $this->helper->redirectHome();
        }
    }
}
