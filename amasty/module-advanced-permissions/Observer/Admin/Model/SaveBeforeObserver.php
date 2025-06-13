<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Observer\Admin\Model;

use Amasty\Rolepermissions\Helper\Data;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Model\AbstractModel;

class SaveBeforeObserver implements ObserverInterface
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var array
     */
    private $classesToCheck;

    public function __construct(
        Data $helper,
        RequestInterface $request,
        array $classesToCheck = []
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->classesToCheck = $classesToCheck;
    }

    public function execute(Observer $observer)
    {
        if ($this->request->getModuleName() == 'api') {
            return;
        }

        /** @var AbstractModel $object */
        $object = $observer->getObject();

        foreach ($this->classesToCheck as $class) {
            if (is_a($object, $class)) {
                return;
            }
        }
        $rule = $this->helper->currentRule();

        if ($rule && $rule->getScopeStoreviews()) {
            if ($object->getId()) {
                $this->helper->restrictObjectByStores($object->getOrigData());
            }

            $this->helper->alterObjectStores($object);
        }
    }
}
