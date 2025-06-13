<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Model\OptionSource;

use Magento\Email\Model\ResourceModel\Template\CollectionFactory as TemplatesCollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class EmailTemplates implements OptionSourceInterface
{
    /**
     * @var TemplatesCollectionFactory
     */
    private $templatesCollectionFactory;

    /**
     * @var array
     */
    private $origTemplateCodes;

    public function __construct(
        TemplatesCollectionFactory $templatesCollectionFactory,
        $origTemplateCodes = []
    ) {
        $this->templatesCollectionFactory = $templatesCollectionFactory;
        $this->origTemplateCodes = $origTemplateCodes;
    }

    public function toOptionArray()
    {
        $collection = $this->templatesCollectionFactory->create()
            ->addFieldToFilter('orig_template_code', ['in' => $this->origTemplateCodes]);

        return $collection->toOptionArray();
    }
}
