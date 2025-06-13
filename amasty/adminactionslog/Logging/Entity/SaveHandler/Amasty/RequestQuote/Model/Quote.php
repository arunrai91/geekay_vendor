<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Amasty\RequestQuote\Model;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Logging\Util\Ignore\ArrayFilter;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;

class Quote extends Common
{
    public const CATEGORY = 'amasty_quote/quote/view';

    /**
     * @var string[]
     */
    private $keysToCheck;

    public function __construct(
        ArrayFilter\ScalarValueFilter $scalarValueFilter,
        ArrayFilter\KeyFilter $keyFilter,
        array $keysToCheck = []
    ) {
        parent::__construct($scalarValueFilter, $keyFilter);
        $this->keysToCheck = $keysToCheck;
    }

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Amasty\RequestQuote\Model\Quote $quote */
        $quote = $metadata->getObject();

        return [
            LogEntry::ITEM => __('Request Quote #%1', $quote->getIncrementId()),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('Request Quote'),
            LogEntry::ELEMENT_ID => $quote->getId(),
            LogEntry::PARAMETER_NAME => 'quote_id'
        ];
    }

    /**
     * @param \Amasty\RequestQuote\Model\Quote $object
     * @return array
     */
    public function processBeforeSave($object): array
    {
        $quote = clone $object;
        $quote->load($object->getId());

        return $this->filterObjectData((array)$quote->getData());
    }

    protected function filterObjectData(array $data): array
    {
        $data = parent::filterObjectData($data);

        return array_intersect_key($data, array_flip($this->keysToCheck));
    }
}
