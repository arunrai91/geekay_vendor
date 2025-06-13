<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Block\Adminhtml\System\Config\InformationBlocks;

use Amasty\ShopbyBase\Block\Adminhtml\System\Config\InformationBlocks\Validation\MessageValidatorInterface;
use Magento\Framework\View\Element\Template;

class Notification extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_ShopbyBase::system/config/information/notification.phtml';

    /**
     * @var MessageValidatorInterface
     */
    private $messageValidator;

    /**
     * @var string
     */
    private $notificationText;

    /**
     * @var bool
     */
    private $isEscapeNeed;

    public function __construct(
        Template\Context $context,
        MessageValidatorInterface $messageValidator,
        string $notificationText = null,
        array $data = [],
        bool $isEscapeNeed = true
    ) {
        parent::__construct($context, $data);

        $this->messageValidator = $messageValidator;
        $this->notificationText = $notificationText;
        $this->isEscapeNeed = $isEscapeNeed;
    }

    public function getNotificationText(): string
    {
        return $this->notificationText;
    }

    public function isDisplayMessage(): bool
    {
        return $this->messageValidator->isValid();
    }

    public function isEscapeNeeded(): bool
    {
        return $this->isEscapeNeed;
    }
}
