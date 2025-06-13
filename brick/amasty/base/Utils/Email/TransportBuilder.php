<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Utils\Email;

use Amasty\Base\Model\MagentoVersion;
use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\AddressConverter;
use Magento\Framework\Mail\EmailMessageInterfaceFactory;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\MessageInterfaceFactory;
use Magento\Framework\Mail\MimeInterface;
use Magento\Framework\Mail\MimeMessageInterface;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Magento\Framework\Mail\MimePartInterface;
use Magento\Framework\Mail\MimePartInterfaceFactory;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\Template\TransportBuilder as ParentTransportBuilder;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;

/**
 * Transport Builder that allows to add attachments to Email
 */
class TransportBuilder extends ParentTransportBuilder
{
    /**
     * @var MessageInterfaceFactory
     */
    private $messageFactory;

    /**
     * @var MultipartMimeMessageFactory
     */
    private $multipartMimeMessageFactory;

    /**
     * @var MimeMessageInterfaceFactory
     */
    private $mimeMessageInterfaceFactory;

    /**
     * @var EmailMessageInterfaceFactory
     */
    private $emailMessageInterfaceFactory;

    /**
     * @var MimePartInterfaceFactory
     */
    private $mimePartInterfaceFactory;

    /**
     * @var AddressConverter
     */
    private $addressConverter;

    /**
     * @var MagentoVersion
     */
    private $magentoVersion;

    /**
     * @var string
     */
    private $bodyHtml;

    /**
     * @var array
     */
    private $messageData = [];

    /**
     * @var array
     */
    private $attachments = [];

    public function __construct(
        FactoryInterface $templateFactory,
        MessageInterface $message,
        SenderResolverInterface $senderResolver,
        ObjectManagerInterface $objectManager,
        TransportInterfaceFactory $mailTransportFactory,
        MessageInterfaceFactory $messageFactory,
        AddressConverter $addressConverter,
        MultipartMimeMessageFactory $multipartMimeMessageFactory,
        MimeMessageInterfaceFactory $mimeMessageInterfaceFactory,
        EmailMessageInterfaceFactory $emailMessageInterfaceFactory,
        MimePartInterfaceFactory $mimePartInterfaceFactory,
        MagentoVersion $magentoVersion
    ) {
        parent::__construct(
            $templateFactory,
            $message,
            $senderResolver,
            $objectManager,
            $mailTransportFactory
        );
        $this->messageFactory = $messageFactory;
        $this->multipartMimeMessageFactory = $multipartMimeMessageFactory;
        $this->mimeMessageInterfaceFactory = $mimeMessageInterfaceFactory;
        $this->emailMessageInterfaceFactory = $emailMessageInterfaceFactory;
        $this->mimePartInterfaceFactory = $mimePartInterfaceFactory;
        $this->magentoVersion = $magentoVersion;
        $this->addressConverter = $addressConverter;
    }

    /**
     * @param string $content
     * @param string|null $filename
     * @param string $type
     * @param string $encoding
     * @return TransportBuilder
     */
    public function addAttachment(
        string $content,
        ?string $filename,
        string $type = 'application/pdf',
        string $encoding = MimeInterface::ENCODING_BASE64
    ): TransportBuilder {
        if ($content) {
            $attachmentPart = $this->mimePartInterfaceFactory->create([
                'content' => $content,
                'type' => $type,
                'fileName' => $filename,
                'encoding' => $encoding,
                'disposition' => MimeInterface::DISPOSITION_ATTACHMENT
            ]);
            $this->attachments[] = $attachmentPart;
        }

        return $this;
    }

    public function setSubject(string $subject): TransportBuilder
    {
        $this->messageData['subject'] = $subject;

        return $this;
    }

    public function setBodyHtml(string $body): TransportBuilder
    {
        $this->bodyHtml = $body;

        return $this;
    }

    public function addTo($address, $name = ''): TransportBuilder
    {
        $this->addAddressByType('to', $address, $name);

        return $this;
    }

    public function setReplyTo($email, $name = null): TransportBuilder
    {
        $this->addAddressByType('replyTo', $email, $name);

        return $this;
    }

    public function addBcc($address): TransportBuilder
    {
        $this->addAddressByType('bcc', $address);

        return $this;
    }

    public function addCc($address, $name = ''): TransportBuilder
    {
        $this->addAddressByType('cc', $address, $name);

        return $this;
    }

    public function setFrom($from): TransportBuilder
    {
        return $this->setFromByScope($from);
    }

    /**
     * @param string|array $from
     * @param int|null $scopeId
     * @return $this
     */
    public function setFromByScope($from, $scopeId = null): TransportBuilder
    {
        $from = $this->_senderResolver->resolve($from, $scopeId);
        $this->addAddressByType('from', $from['email'], $from['name']);

        return $this;
    }

    protected function prepareMessage(): TransportBuilder
    {
        $bodyMimePart = $this->prepareBodyMimePart();
        $this->messageData['encoding'] = $bodyMimePart->getCharset();
        $this->messageData['body'] = $this->createMimeMessage(array_merge([$bodyMimePart], $this->attachments));
        $this->message = $this->emailMessageInterfaceFactory->create($this->messageData);

        return $this;
    }

    protected function reset(): TransportBuilder
    {
        parent::reset();
        $this->message = $this->messageFactory->create();
        $this->messageData = [];
        $this->attachments = [];

        return $this;
    }

    /**
     * @param MimePartInterface[] $parts
     * @return MimeMessageInterface
     */
    private function createMimeMessage(array $parts): MimeMessageInterface
    {
        if (version_compare($this->magentoVersion->get(), '2.4.8') >= 0) {
            return $this->multipartMimeMessageFactory->create(['parts' => $parts]);
        }

        return $this->mimeMessageInterfaceFactory->create(['parts' => $parts]);
    }

    /**
     * @param string $addressType
     * @param string|array $email
     * @param string|null $name
     */
    private function addAddressByType(string $addressType, $email, ?string $name = ''): void
    {
        if (is_string($email)) {
            $this->messageData[$addressType][] = $this->addressConverter->convert($email, $name);
            return;
        }
        $convertedAddressArray = $this->addressConverter->convertMany($email);
        if (isset($this->messageData[$addressType])) {
            $this->messageData[$addressType] = array_merge(
                $this->messageData[$addressType],
                $convertedAddressArray
            );
        } else {
            $this->messageData[$addressType] = $convertedAddressArray;
        }
    }

    /**
     * @throws LocalizedException
     */
    private function prepareBodyMimePart(): MimePartInterface
    {
        if ($this->bodyHtml !== null) {
            $content = $this->bodyHtml;
            $partType = MimeInterface::TYPE_HTML;
        } else {
            $template = $this->getTemplate();
            $content = $template->processTemplate();
            switch ($template->getType()) {
                case TemplateTypesInterface::TYPE_TEXT:
                    $partType = MimeInterface::TYPE_TEXT;
                    break;
                case TemplateTypesInterface::TYPE_HTML:
                    $partType = MimeInterface::TYPE_HTML;
                    break;
                default:
                    throw new LocalizedException(
                        new Phrase('Unknown template type')
                    );
            }
            //phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
            $this->messageData['subject'] = html_entity_decode((string)$template->getSubject(), ENT_QUOTES);
        }

        return $this->mimePartInterfaceFactory->create(['content' => $content, 'type' => $partType]);
    }
}
