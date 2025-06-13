<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Controller\Adminhtml\Geoip;

use Amasty\Geoip\Model\System\Message\LicenseInvalid as LicenseInvalidMessage;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Redirect;

class DismissNotification extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Amasty_Geoip::amasty_geoip';

    /**
     * @var LicenseInvalidMessage
     */
    private $licenseInvalidMessage;

    public function __construct(
        Context $context,
        LicenseInvalidMessage $licenseInvalidMessage
    ) {
        $this->licenseInvalidMessage = $licenseInvalidMessage;
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        $this->licenseInvalidMessage->setIsDisplayed(false);

        return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRefererUrl());
    }
}
