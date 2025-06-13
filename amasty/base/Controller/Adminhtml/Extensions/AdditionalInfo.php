<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Controller\Adminhtml\Extensions;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Amasty\Base\Model\Extensions\AdditionalInfoProvider;

class AdditionalInfo extends Action
{
    /**
     * @var AdditionalInfoProvider
     */
    private $additionalInfoProvider;

    public function __construct(
        Context $context,
        AdditionalInfoProvider $additionalInfoProvider
    ) {
        parent::__construct($context);

        $this->additionalInfoProvider = $additionalInfoProvider;
    }

    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData($this->additionalInfoProvider->get());

        return $result;
    }
}
