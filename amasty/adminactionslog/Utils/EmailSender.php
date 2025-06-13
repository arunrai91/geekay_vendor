<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Utils;

use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

class EmailSender
{
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        TransportBuilder $transportBuilder,
        LoggerInterface $logger
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
    }

    public function sendEmail(
        array $recipients = [],
        string $templateIdentifier = '',
        string $sendFrom = 'general',
        array $vars = [],
        string $area = Area::AREA_FRONTEND,
        int $storeId = Store::DEFAULT_STORE_ID
    ) {
        try {
            foreach ($recipients as $recipient) {
                $transportBuild = $this->transportBuilder->setTemplateIdentifier($templateIdentifier)
                    ->setTemplateOptions(['area' => $area, 'store' => $storeId])
                    ->setTemplateVars($vars)
                    ->setFromByScope($sendFrom, $storeId)
                    ->addTo($recipient);
                $transportBuild->getTransport()->sendMessage();
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
