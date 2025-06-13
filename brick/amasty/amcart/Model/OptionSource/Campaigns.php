<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Model\OptionSource;

use Amasty\Acart\Model\ResourceModel\Rule\CollectionFactory;
use Amasty\Acart\Ui\DataProvider\Reports\FilterConstants;
use Magento\Framework\Data\OptionSourceInterface;

class Campaigns implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var array
     */
    private $options;

    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    public function toOptionArray(): array
    {
        $campaigns = $this->getCampaignsList();
        array_unshift($campaigns, ['value' => FilterConstants::ALL, 'label' => __('All Campaigns')]);

        return $campaigns;
    }

    public function getCampaignsList(): array
    {
        if (!$this->options) {
            $collection = $this->collectionFactory->create();
            $this->options = $collection->toOptionArray();
        }

        return $this->options;
    }
}
