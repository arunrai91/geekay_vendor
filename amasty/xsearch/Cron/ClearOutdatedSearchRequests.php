<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Cron;

use Amasty\Xsearch\Model\Config;
use Amasty\Xsearch\Model\ResourceModel\UserSearch as UserSearchResource;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class ClearOutdatedSearchRequests
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var UserSearchResource
     */
    private $userSearchResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Config $config,
        UserSearchResource $userSearchResource,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->userSearchResource = $userSearchResource;
        $this->logger = $logger;
    }

    public function execute(): void
    {
        if ($this->config->isUserSearchAutocleaningEnabled()) {
            try {
                $this->userSearchResource->deleteUserSearchOlderThan($this->config->getUserSearchCleaningPeriod());
            } catch (LocalizedException $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
