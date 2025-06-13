<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model;

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;

class OptionSettingFactory
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
     * Provide Option Setting instance
     *
     * @param array $arguments
     * @return OptionSettingInterface
     * @throws \UnexpectedValueException
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create(OptionSettingInterface::class, $arguments);
    }
}
