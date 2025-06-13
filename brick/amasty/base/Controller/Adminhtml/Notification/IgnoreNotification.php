<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Controller\Adminhtml\Notification;

use Amasty\Base\Model\FlagRepository;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class IgnoreNotification extends Action
{
    /**
     * @var FlagRepository
     */
    private $flagRepository;

    public function __construct(
        Context $context,
        FlagRepository $flagRepository
    ) {
        parent::__construct($context);
        $this->flagRepository = $flagRepository;
    }

    public function execute()
    {
        $identity = $this->getRequest()->getParam('identity');
        if ($identity) {
            $this->flagRepository->save($identity, '1');
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setRefererUrl();
    }
}
