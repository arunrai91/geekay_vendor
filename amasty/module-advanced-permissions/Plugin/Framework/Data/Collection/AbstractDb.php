<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Plugin\Framework\Data\Collection;

use Amasty\Rolepermissions\Helper\Data;

class AbstractDb
{
    /**
     * @var \Amasty\Rolepermissions\Helper\Data\Proxy $helper
     */
    private $helper;

    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    public function beforeGetSize($subject)
    {
        if ($subject instanceof \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection) {
            $restrictedAttributeIds = $this->helper->getRestrictedAttributeIds();

            if ($restrictedAttributeIds) {
                $restrictedSetIds = $this->helper->getRestrictedSetIds();
                $subject->addFieldToFilter('attribute_set_id', ['nin' => $restrictedSetIds]);
            }
        } elseif ($subject instanceof \Magento\Sales\Model\ResourceModel\Order\Customer\Collection) {
            $scopeStoreviews = $this->helper->currentRule()->getScopeStoreviews();

            if (!empty($scopeStoreviews)) {
                $subject->addAttributeToFilter('store_id', ['in' => $scopeStoreviews]);
            }
        }
    }

    public function afterGetData($subject, $result)
    {
        if ($subject instanceof \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection) {
            if (empty($result)) {
                $result[] = ['value' => '', 'label' => ''];
            }
        }

        return $result;
    }

    public function beforeGetData($subject)
    {
        if ($subject instanceof \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection) {
            $restrictedAttributeIds = $this->helper->getRestrictedAttributeIds();

            if ($restrictedAttributeIds) {
                $restrictedSetIds = $this->helper->getRestrictedSetIds();
                $subject->addFieldToFilter('attribute_set_id', ['nin' => $restrictedSetIds]);
            }
        }
    }
}
