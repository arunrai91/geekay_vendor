<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Observer\Admin;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

class SystemConfig implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * SystemConfig constructor.
     *
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        ManagerInterface $messageManager
    ) {
        $this->messageManager = $messageManager;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $section = $observer->getRequest()->getParam('section');
        if ($section == 'amgeoip') {
            $this->messageManager->addWarningMessage(
                __('When import in progress please do not close this '
                . 'browser window and do not attempt to operate Magento backend in separate tabs. '
                . 'Import usually takes from 10 to 20 minutes.')
            );

            $this->messageManager->addNoticeMessage(
                __('Please note that you need to import the database first. '
                    . 'The database included in the extension was last updated on October 24, 2023. '
                    . 'To access the most recent IP data, you can either upload it manually '
                    . 'or use our Amasty Service to refresh the IP Database.')
            );
        }
    }
}
