<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler\CheckoutAgreement;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Logging\Util\Ignore\ArrayFilter;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;
use Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface;
use Magento\CheckoutAgreements\Model\Agreement as CheckoutAgreement;

class Agreement extends Common
{
    public const CATEGORY = 'checkout/agreement/edit';

    /**
     * @var string[]
     */
    protected $dataKeysIgnoreList = [
        'form_key'
    ];

    /**
     * @var CheckoutAgreementsRepositoryInterface
     */
    private $checkoutAgreementsRepository;

    public function __construct(
        ArrayFilter\ScalarValueFilter $scalarValueFilter,
        ArrayFilter\KeyFilter $keyFilter,
        CheckoutAgreementsRepositoryInterface $checkoutAgreementsRepository
    ) {
        parent::__construct($scalarValueFilter, $keyFilter);

        $this->checkoutAgreementsRepository = $checkoutAgreementsRepository;
    }

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var CheckoutAgreement $agreement */
        $agreement = $metadata->getObject();

        return [
            LogEntry::ITEM => $agreement->getName(),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('Terms and Conditions'),
            LogEntry::ELEMENT_ID => (int)$agreement->getId(),
        ];
    }

    public function processBeforeSave($object): array
    {
        if ($object->getId()) {
            $agreement = $this->checkoutAgreementsRepository->get((int)$object->getId());

            return $this->filterObjectData($agreement->getData());
        }

        return [];
    }
}
