<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model;

use Laminas\Validator\EmailAddress;
use Magento\Framework\App\Area;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class EmailManagement extends AbstractModel
{
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var EmailAddress
     */
    private $emailAddressValidator;

    public function __construct(
        TransportBuilder $transportBuilder,
        Config $config,
        Context $context,
        Registry $registry,
        TimezoneInterface $timezone,
        EmailAddress $emailAddressValidator,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->config = $config;
        $this->timezone = $timezone;
        $this->emailAddressValidator = $emailAddressValidator;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @param Feed $feed
     * @param string $emailTemplate
     * @param null|string $errorMessage
     */
    private function prepareSendEmail($feed, $emailTemplate, $errorMessage)
    {
        $emails = $this->config->getEmails();
        foreach ($emails as $key => $email) {
            if (!$this->emailAddressValidator->isValid($email)) {
                unset($emails[$key]);
            }
        }

        $templateVars = [
            'feed_id' => $feed->getEntityId(),
            'feed_name' => $feed->getName(),
            'date_time' => $this->timezone->date($feed->getGeneratedAt())->format('Y-m-d H:i:s'),
            'generation_error' => $errorMessage
        ];
        $emailSenderContact = $this->config->getEmailSenderContact();
        $storeId = $feed->getStoreId();

        $transport = $this->transportBuilder->setTemplateIdentifier(
            $emailTemplate
        )->setTemplateOptions(
            ['area' => Area::AREA_FRONTEND, 'store' => $storeId]
        )->setFromByScope(
            $emailSenderContact,
            $storeId
        )->setTemplateVars(
            $templateVars
        )->addTo(
            $emails
        )->getTransport();

        $this->setTransport($transport);
    }

    /**
     * @param Feed $feed
     * @param string $emailTemplate
     * @param null $errorMessage
     * @return $this
     */
    public function sendEmail($feed, $emailTemplate, $errorMessage = null)
    {
        $this->prepareSendEmail($feed, $emailTemplate, $errorMessage);
        try {
            $this->getTransport()->sendMessage();
        } catch (MailException $e) {
            $this->_logger->critical($e);
        }

        return $this;
    }
}
