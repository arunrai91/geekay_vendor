<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Controller\Adminhtml\RelevanceRule;

use Amasty\ElasticSearch\Api\Data\RelevanceRuleInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\App\Action;

class Delete extends Action
{
    public const ADMIN_RESOURCE = 'Amasty_ElasticSearch::relevance_rules';

    /**
     * @var \Amasty\ElasticSearch\Model\RelevanceRuleRepository
     */
    private $relevanceRuleRepository;

    /**
     * @var \Amasty\ElasticSearch\Model\ResourceModel\RelevanceRule\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Filter
     */
    private $filter;

    public function __construct(
        Action\Context $context,
        \Amasty\ElasticSearch\Model\RelevanceRuleRepository $relevanceRuleRepository,
        \Amasty\ElasticSearch\Model\ResourceModel\RelevanceRule\CollectionFactory $collectionFactory,
        Filter $filter
    ) {
        parent::__construct($context);

        $this->relevanceRuleRepository = $relevanceRuleRepository;
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        if ($this->getRequest()->getParam(Filter::SELECTED_PARAM)
            || $this->getRequest()->getParam(Filter::EXCLUDED_PARAM)
        ) {
            $ids = $this->filter->getCollection($this->collectionFactory->create())->getAllIds();
        } else {
            $ids = (array)$this->getRequest()->getParam(RelevanceRuleInterface::RULE_ID);
        }

        if ($ids) {
            foreach ($ids as $id) {
                try {
                    $this->relevanceRuleRepository->deleteById($id);
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
            }

            $this->messageManager->addSuccessMessage(
                __('%1 rule(s) was successfully removed', count($ids))
            );
        } else {
            $this->messageManager->addErrorMessage(__('Please select Relevance Rule(s)'));
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
