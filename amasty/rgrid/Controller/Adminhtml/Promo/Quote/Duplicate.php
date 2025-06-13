<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Promotions Manager for Magento 2
 */

namespace Amasty\Rgrid\Controller\Adminhtml\Promo\Quote;

use Amasty\Rgrid\Model\DuplicateRuleProcessor;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;

class Duplicate extends Action
{
    /**
     * @var DuplicateRuleProcessor
     */
    private $processDuplicateRule;

    public function __construct(
        Action\Context $context,
        DuplicateRuleProcessor $processDuplicateRule
    ) {
        parent::__construct($context);
        $this->processDuplicateRule = $processDuplicateRule;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $ruleId = (int)$this->getRequest()->getParam('id');

        if ($ruleId) {
            try {
                $newRule = $this->processDuplicateRule->execute($ruleId);
                $this->messageManager->addSuccessMessage(__('The rule has been duplicated.'));

                return $resultRedirect->setPath('sales_rule/*/edit', ['id' => $newRule->getRuleId()]);
            } catch (LocalizedException $exception) {
                $this->messageManager->addExceptionMessage($exception);
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('We can\'t duplicate the rule right now. Please review the log and try again.')
                );
            }

            return $resultRedirect->setPath('sales_rule/*/');
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a rule to duplicate.'));

        return $resultRedirect->setPath('sales_rule/*/');
    }
}
