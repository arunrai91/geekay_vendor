<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Controller\Adminhtml\RelevanceRule;

class NewConditionHtml extends AbstractRelevance
{
    /**
     * @return void
     */
    public function execute()
    {
        $ruleId = $this->getRequest()->getParam('id');
        $formName = $this->getRequest()->getParam('form_namespace');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = $this->_objectManager->create($type)
            ->setId($ruleId)
            ->setType($type)
            ->setRule($this->_objectManager->create(\Magento\CatalogRule\Model\Rule::class))
            ->setPrefix('conditions');

        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof \Magento\Rule\Model\Condition\AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $model->setFormName($formName);
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }

        $this->getResponse()->setBody($html);

        return $this->getResponse();
    }
}
