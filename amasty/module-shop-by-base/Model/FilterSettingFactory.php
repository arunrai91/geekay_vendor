<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model;

use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;

class FilterSettingFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Provide Filter Setting instance
     *
     * @param array $arguments
     * @return FilterSettingInterface
     * @throws \UnexpectedValueException
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create(FilterSettingInterface::class, $arguments);
    }
}
