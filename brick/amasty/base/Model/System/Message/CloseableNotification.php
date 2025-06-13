<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Model\System\Message;

use Amasty\Base\Model\FlagRepository;
use Amasty\Base\Model\System\Message\DisplayValidator\DisplayValidatorInterface;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\UrlInterface;

class CloseableNotification implements MessageInterface
{
    public const CLOSE_LINK_PLACEHOLDER = '{{closeLink}}';

    private const IGNORE_NOTIFICATION_PATH = 'ambase/notification/ignoreNotification';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var FlagRepository
     */
    private $flagRepository;

    /**
     * @var DisplayValidatorInterface
     */
    private $displayValidator;

    /**
     * @var string
     */
    private $identity;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $closeLinkText;

    /**
     * @var int
     */
    private $severity;

    public function __construct(
        UrlInterface $urlBuilder,
        FlagRepository $flagRepository,
        DisplayValidatorInterface $displayValidator,
        string $identity,
        string $message,
        string $closeLinkText = '',
        int $severity = MessageInterface::SEVERITY_MAJOR
    ) {
        $this->displayValidator = $displayValidator;
        $this->identity = $identity;
        $this->message = $message;
        $this->closeLinkText = $closeLinkText;
        $this->severity = $severity;
        $this->urlBuilder = $urlBuilder;
        $this->flagRepository = $flagRepository;
    }

    public function getIdentity(): string
    {
        return $this->identity;
    }

    public function isDisplayed(): bool
    {
        return !$this->flagRepository->get($this->getIdentity() . '_ignored')
            && $this->displayValidator->needToShow();
    }

    public function getText(): string
    {
        $this->addCloseLink($this->message);

        return $this->message;
    }

    public function getSeverity(): int
    {
        return $this->severity;
    }

    private function addCloseLink(string &$message): void
    {
        if ($this->closeLinkText && stripos($message, self::CLOSE_LINK_PLACEHOLDER) !== false) {
            $closeLink = $this->urlBuilder->getUrl(
                self::IGNORE_NOTIFICATION_PATH,
                ['identity' => $this->getIdentity() . '_ignored']
            );
            $closeLinkText = str_replace('href=""', 'href="' . $closeLink . '"', $this->closeLinkText);
            $message = str_replace(self::CLOSE_LINK_PLACEHOLDER, $closeLinkText, $message);
        }
    }
}
