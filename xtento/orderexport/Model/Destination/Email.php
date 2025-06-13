<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2023-03-18T19:37:46+00:00
 * File:          Model/Destination/Email.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Destination;

class Email extends AbstractClass
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * Email constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Xtento\OrderExport\Model\DestinationFactory $destinationFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Xtento\OrderExport\Model\DestinationFactory $destinationFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $objectManager, $scopeConfig, $dateTime, $filesystem, $encryptor, $destinationFactory, $resource, $resourceCollection, $data);
        $this->localeDate = $localeDate;
    }


    public function testConnection()
    {
        $this->initConnection();
        if (!$this->getDestination()->getBackupDestination()) {
            $this->getDestination()->setLastResult($this->getTestResult()->getSuccess())->setLastResultMessage($this->getTestResult()->getMessage())->save();
        }
        return $this->getTestResult();
    }

    public function initConnection()
    {
        $this->setDestination($this->destinationFactory->create()->load($this->getDestination()->getId()));
        $testResult = new \Magento\Framework\DataObject();
        $this->setTestResult($testResult);
        $this->getTestResult()->setSuccess(true)->setMessage(__('Ready to send emails.'));
        return true;
    }

    public function saveFiles($fileArray, $recursiveCall = false)
    {
        if (empty($fileArray)) {
            return [];
        }

        // Send each file as separate email
        if (!$recursiveCall && $this->getDestination()->getEmailSendFilesSeparately()) {
            $savedFiles = [];
            foreach ($fileArray as $filename => $data) {
                $savedFiles[] = $filename;
                self::saveFiles([$filename => $data], true);
            }
            return $savedFiles;
        }

        // Init connection
        $this->initConnection();
        $savedFiles = [];
        $utilsHelper = $this->objectManager->create('\Xtento\XtCore\Helper\Utils');

        $bodyFiles = [];
        foreach ($fileArray as $filename => $data) {
            $savedFiles[] = $filename;
            if (stripos($filename, '.pdf') === false) {
                $bodyFiles[] = $data;
            }
        }

        $senderEmail = $this->getDestination()->getEmailSender();
        $senderFromName = $this->getDestination()->getEmailSender();
        if (stristr($senderEmail, '|') !== false) {
            $splitValue = explode("|", $senderEmail);
            $senderEmail = $splitValue[0];
            $senderFromName = $splitValue[1];
        }

        // Magento >= 2.3.3
        if (version_compare($utilsHelper->getMagentoVersion(), '2.3.3', '>=')) {
            /** @var \Magento\Framework\Mail\EmailMessage $message */
            if (@class_exists('\Zend\Mime\Part')) {
                $text = new \Zend\Mime\Part(strip_tags($this->replaceVariables($this->getDestination()->getEmailBody(), implode("\n\n", $bodyFiles))));
            } else {
                $text = new \Laminas\Mime\Part(strip_tags($this->replaceVariables($this->getDestination()->getEmailBody(), implode("\n\n", $bodyFiles))));
            }
            $text->type = 'text/plain';
            $text->charset = 'utf-8';

            $htmlText = $this->replaceVariables($this->getDestination()->getEmailBody(), implode("\n\n", $bodyFiles));
            if (strstr($htmlText, '<') === false) {
                $htmlText = nl2br($htmlText);
            }
            if (@class_exists('\Zend\Mime\Part')) {
                $html = new \Zend\Mime\Part($htmlText);
            } else {
                $html = new \Laminas\Mime\Part($htmlText);
            }
            $html->type = 'text/html';
            $html->charset = 'utf-8';

            if (@class_exists('\Zend\Mime\Message')) {
                $content = new \Zend\Mime\Message();
            } else {
                $content = new \Laminas\Mime\Message();
            }
            $content->setParts([$text, $html]);
            if (@class_exists('\Zend\Mime\Part')) {
                $contentPart = new \Zend\Mime\Part($content->generateMessage());
            } else {
                $contentPart = new \Laminas\Mime\Part($content->generateMessage());
            }
            $contentPart->type = 'multipart/alternative;' . "\r\n" . ' boundary="' . $content->getMime()->boundary() . '"';

            $messageParts = [$contentPart];
            foreach ($fileArray as $filename => $data) {
                if ($this->getDestination()->getEmailAttachFiles()) {
                    $attachment = $this->objectManager->create('Magento\Framework\Mail\MimePartInterfaceFactory')->create(
                        [
                            'content' => $data,
                            'fileName' => $filename,
                            'disposition' => 'attachment',
                            'encoding' => 'base64'
                        ]
                    );
                    array_push($messageParts, $attachment);
                }
            }

            $messageData['body'] = $this->objectManager->create('Magento\Framework\Mail\MimeMessageInterfaceFactory')->create(
                ['parts' => $messageParts]
            );
            if ($senderEmail === $senderFromName) {
                $senderFromName = '';
            }
            $messageData['from'] = [$this->objectManager->create('Magento\Framework\Mail\AddressFactory')->create(['email' => $senderEmail, 'name' => $senderFromName])];
            foreach (explode(",", $this->getDestination()->getEmailRecipient()) as $email) {
                $email = trim($email);
                $email = $this->replaceVariables($email, '');
                $messageData['to'][] = $this->objectManager->create('Magento\Framework\Mail\AddressFactory')->create(['email' => $email, 'name' => '']);
            }
            $bccRecipients = $this->getDestination()->getEmailBcc();
            if (!empty($bccRecipients)) {
                foreach (explode(",", $bccRecipients) as $email) {
                    $email = trim($email);
                    $email = $this->replaceVariables($email, '');
                    $messageData['bcc'][] = $this->objectManager->create('Magento\Framework\Mail\AddressFactory')->create(['email' => $email, 'name' => '']);
                }
            }
            $messageData['subject'] = $this->replaceVariables($this->getDestination()->getEmailSubject(), implode("\n\n", $bodyFiles));
            $mail = $this->objectManager->create('Magento\Framework\Mail\EmailMessageInterfaceFactory')->create($messageData);
        } else {
            /** @var \Magento\Framework\Mail\Message $message */
            $mail = $this->objectManager->create('Magento\Framework\Mail\MessageInterface');

            if (method_exists($mail, 'setFromAddress')) {
                $mail->setFromAddress($senderEmail, $senderFromName);
            } else {
                $mail->setFrom($senderEmail, $senderFromName);
            }
            foreach (explode(",", $this->getDestination()->getEmailRecipient()) as $email) {
                $email = trim($email);
                $email = $this->replaceVariables($email, '');
                $mail->addTo($email, '=?utf-8?B?' . base64_encode($email) . '?=');
            }
            $bccRecipients = $this->getDestination()->getEmailBcc();
            if (!empty($bccRecipients)) {
                foreach (explode(",", $bccRecipients) as $email) {
                    $email = trim($email);
                    $email = $this->replaceVariables($email, '');
                    $mail->addBcc($email, '=?utf-8?B?' . base64_encode($email) . '?=');
                }
            }

            #$mail->setSubject($this->_replaceVariables($this->getDestination()->getEmailSubject(), $firstFileContent));
            $mail->setSubject('=?utf-8?B?' . base64_encode($this->replaceVariables($this->getDestination()->getEmailSubject(), implode("\n\n", $bodyFiles))) . '?=');

            if (version_compare($utilsHelper->getMagentoVersion(), '2.2.8', '>=')) {
                // >= M2.3: ZF1 removed in M2.3
                $text = new \Zend\Mime\Part(strip_tags($this->replaceVariables($this->getDestination()->getEmailBody(), implode("\n\n", $bodyFiles))));
                $text->type = 'text/plain';
                $text->charset = 'utf-8';

                $htmlText = $this->replaceVariables($this->getDestination()->getEmailBody(), implode("\n\n", $bodyFiles));
                if (strstr($htmlText, '<') === false) {
                    $htmlText = nl2br($htmlText);
                }
                $html = new \Zend\Mime\Part($htmlText);
                $html->type = 'text/html';
                $html->charset = 'utf-8';

                $content = new \Zend\Mime\Message();
                $content->setParts([$text, $html]);
                $contentPart = new \Zend\Mime\Part($content->generateMessage());
                $contentPart->type = 'multipart/alternative;' . "\r\n" . ' boundary="' . $content->getMime()->boundary() . '"';

                $messageParts = [$contentPart];
                foreach ($fileArray as $filename => $data) {
                    if ($this->getDestination()->getEmailAttachFiles()) {
                        $attachment = new \Zend\Mime\Part($data);
                        $attachment->filename = $filename;
                        $attachment->disposition ='attachment';
                        $attachment->encoding = 'base64';
                        array_push($messageParts, $attachment);
                    }
                }

                $mimeMessage = new \Zend\Mime\Message();
                $mimeMessage->setParts($messageParts);
                $mail->setBody($mimeMessage);
            } else {
                // <=M2.2.7
                foreach ($fileArray as $filename => $data) {
                    if ($this->getDestination()->getEmailAttachFiles()) {
                        $attachment = $mail->createAttachment($data);
                        $attachment->filename = $filename;
                    }
                }

                $mail->setMessageType(\Magento\Framework\Mail\Message::TYPE_TEXT)
                    ->setBody(strip_tags($this->replaceVariables($this->getDestination()->getEmailBody(), implode("\n\n", $bodyFiles))));
                $mail->setMessageType(\Magento\Framework\Mail\Message::TYPE_HTML)
                    ->setBody(nl2br($this->replaceVariables($this->getDestination()->getEmailBody(), implode("\n\n", $bodyFiles))));
            }
        }

        try {
            $this->objectManager->create('\Magento\Framework\Mail\TransportInterfaceFactory')->create(['message' => clone $mail])->sendMessage();
        } catch (\Exception $e) {
            $this->getTestResult()->setSuccess(false)->setMessage(__('Error while sending email: %1', $e->getMessage()));
            return false;
        }

        return $savedFiles;
    }

    protected function replaceVariables($string, $content)
    {
        $additionalVariables = $this->_registry->registry('xtento_orderexport_export_variables');
        $additionalVariables = is_array($additionalVariables) ? $additionalVariables : [];

        $replaceableVariables = [
            '%d%' => $this->localeDate->date()->format('d'),
            '%m%' => $this->localeDate->date()->format('m'),
            '%y%' => $this->localeDate->date()->format('y'),
            '%Y%' => $this->localeDate->date()->format('Y'),
            '%h%' => $this->localeDate->date()->format('H'),
            '%i%' => $this->localeDate->date()->format('i'),
            '%s%' => $this->localeDate->date()->format('s'),
            '%exportid%' => ($this->_registry->registry('orderexport_log')) ? $this->_registry->registry('orderexport_log')->getId() : 0,
            '%lastexportedincrementid%' => isset($additionalVariables['/%lastincrementid%/']) ? $additionalVariables['/%lastincrementid%/'] : 0,
            '%customeremail%' => isset($additionalVariables['/%lastcustomeremail%/']) ? $additionalVariables['/%lastcustomeremail%/'] : '',
            '%recordcount%' => ($this->_registry->registry('orderexport_log')) ? $this->_registry->registry('orderexport_log')->getRecordsExported() : 0,
            '%content%' => $content,
        ];
        $string = str_replace(array_keys($replaceableVariables), array_values($replaceableVariables), $string);
        return $string;
    }
}