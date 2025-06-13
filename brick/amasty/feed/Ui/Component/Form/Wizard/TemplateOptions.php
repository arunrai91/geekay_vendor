<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Ui\Component\Form\Wizard;

use Amasty\Feed\Model\FeedTemplate\ResourceModel\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class TemplateOptions implements OptionSourceInterface
{
    public const CUSTOM_FEED_CODE = 'custom_feed';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    public function toOptionArray(): array
    {
        $options = $this->getOptions();
        array_unshift($options, $this->getDefaultOption());

        return $options;
    }

    public function getDefaultOption(): array
    {
        return [
            'value' => '',
            'label' => __('Please Select...')
        ];
    }

    /**
     * @return array [ ['value' => 'template_code', 'label' => 'template_name'], ... ]
     */
    public function getOptions(): array
    {
        $options[] = [
            'value' => self::CUSTOM_FEED_CODE,
            'label' => __('Custom Feed')
        ];
        foreach ($this->collectionFactory->create() as $template) {
            $options[] = [
                'value' => $template->getTemplateCode(),
                'label' => __($template->getTemplateName())
            ];
        }

        return $options;
    }
}
