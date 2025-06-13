<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Model\Entity\Attribute\Source;

use Magento\User\Model\ResourceModel\User\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\ObjectManagerInterface;

class Admins extends AbstractSource
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    public function getAllOptions()
    {
        if ($this->_options === null) {
            $users = $this->collectionFactory->create();

            $this->_options = [[
                'label' => __('- Not Specified -'),
                'value' => 0
            ]];

            foreach ($users as $user) {
                $this->_options []= [
                    'label' => $user->getFirstname() . ' ' . $user->getLastname(),
                    'value' => $user->getId()
                ];
            }
        }
        return $this->_options;
    }
}
