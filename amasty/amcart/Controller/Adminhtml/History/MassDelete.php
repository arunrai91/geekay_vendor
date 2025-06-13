<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Controller\Adminhtml\History;

use Amasty\Acart\Controller\Adminhtml\History;
use Amasty\Acart\Model\History as HistoryModel;
use Amasty\Acart\Model\ResourceModel\History as HistoryResource;
use Amasty\Acart\Model\ResourceModel\History\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends History implements HttpPostActionInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var HistoryResource
     */
    private $historyResource;

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        Filter $filter,
        HistoryResource $historyResource
    ) {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
        $this->historyResource = $historyResource;
    }

    public function execute(): Redirect
    {
        $this->filter->applySelectionOnTargetProvider();
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        try {
            $this->historyResource->massDelete($collection->getColumnValues(HistoryModel::HISTORY_ID));
            $this->messageManager->addSuccessMessage(
                __('Record(s) has been successfully deleted')
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(
                __('Error occurred while deleting History records. Error: %1'),
                [$e->getMessage()]
            );
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while deleting History records. Please review the logs.')
            );
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }
}
