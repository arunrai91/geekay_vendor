<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Controller\Adminhtml\RelevanceRule;

use Magento\Framework\Controller\ResultFactory;

class Index extends AbstractRelevance
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        return $this->initPage($resultPage);
    }
}
