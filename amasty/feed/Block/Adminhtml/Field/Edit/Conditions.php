<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Block\Adminhtml\Field\Edit;

use Amasty\Base\Model\MagentoVersion;
use Amasty\Feed\Model\Field\ConditionProvider;
use Amasty\Feed\Model\Field\FormProcessor;
use Amasty\Feed\Model\Field\FormProcessorFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Rule\Block\Conditions as ConditionsBlock;
use Magento\Rule\Model\Condition\AbstractCondition;

class Conditions extends Generic
{
    /**
     * Keys for DataPersistor and UI
     */
    public const FORM_NAMESPACE = 'amfeed_field_form';

    /**
     * @var ConditionsBlock
     */
    private $conditionsBlock;

    /**
     * @var Fieldset
     */
    private $fieldset;

    /**
     * @var FormProcessor
     */
    private $formProcessor;

    /**
     * @var ConditionProvider
     */
    private $conditionProvider;

    /**
     * @var MagentoVersion
     */
    private $magentoVersion;

    public function __construct(
        Context $context,
        Registry $registry,
        ConditionsBlock $conditions,
        FormFactory $formFactory,
        Fieldset $fieldset,
        ?ProductMetadataInterface $metadata,  // @deprecated
        FormProcessorFactory $formProcessorFactory,
        ConditionProvider $conditionProvider,
        array $data = [],
        MagentoVersion $magentoVersion = null // TODO move to not optional
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->conditionsBlock = $conditions;
        $this->fieldset = $fieldset;
        $this->formProcessor = $formProcessorFactory->create();
        $this->formProcessor->initialize($this->getLayout());
        $this->conditionProvider = $conditionProvider;
        // OM for backward compatibility
        $this->magentoVersion = $magentoVersion ?? ObjectManager::getInstance()->get(MagentoVersion::class);
    }

    public function toHtml(): string
    {
        if (version_compare($this->magentoVersion->get(), '2.2.0', '>=')) {
            //Fix for Magento >2.2.0 to display right form layout.
            //Result of compatibility with 2.1.x.
            $this->_prepareLayout();
        }

        $condition = $this->conditionProvider->getCondition((int)$this->getRequest()->getParam('id'));
        $conditionsFieldSetId = $condition->getConditionsFieldSetId(self::FORM_NAMESPACE);
        $newChildUrl = $this->getUrl(
            'sales_rule/promo_quote/newConditionHtml/form/' . $conditionsFieldSetId,
            ['form_namespace' => self::FORM_NAMESPACE]
        );

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $renderer = $this->fieldset->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setNewChildUrl($newChildUrl)
            ->setFieldSetId($conditionsFieldSetId)
            ->setNameInLayout('amasty.feed.field.fieldset.conditions');

        $fieldset = $form->addFieldset(
            $conditionsFieldSetId,
            []
        )->setRenderer(
            $renderer
        );

        $fieldset->addField(
            'conditions' . $conditionsFieldSetId,
            'text',
            [
                'name' => 'conditions' . $conditionsFieldSetId,
                'label' => __('Conditions'),
                'title' => __('Conditions'),
                'required' => true,
                'data-form-part' => self::FORM_NAMESPACE,
            ]
        )->setRule($condition)->setRenderer($this->conditionsBlock);
        $this->setConditionFormName(
            $condition->getConditions(),
            self::FORM_NAMESPACE,
            $conditionsFieldSetId
        );

        return $this->formProcessor->execute($form, $condition->getData());
    }

    private function setConditionFormName(
        AbstractCondition $conditions,
        string $formName,
        string $conditionsFieldSetId
    ): void {
        $conditions->setFormName($formName);
        $conditions->setJsFormObject($conditionsFieldSetId);

        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName, $conditionsFieldSetId);
                $condition->setJsFormObject($conditionsFieldSetId);
            }
        }
    }
}
