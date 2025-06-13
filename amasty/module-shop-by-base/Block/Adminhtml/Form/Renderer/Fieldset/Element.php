<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Block\Adminhtml\Form\Renderer\Fieldset;

class Element extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element
{
    public const SCOPE_LABEL = '[STORE VIEW]';

    /**
     * @var string
     */
    protected $_template = 'form/renderer/fieldset/element.phtml';

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getScopeLabel()
    {
        return __('%1', static::SCOPE_LABEL);
    }

    /**
     * @return bool
     */
    public function usedDefault()
    {
        $isDefault = $this->getDataObject()->getData($this->getElement()->getName().'_use_default');
        if ($isDefault === null || $this->getDataObject()->getStoreId() == 0) {
            $isDefault = true;
        }

        return $isDefault;
    }

    /**
     * @return $this
     */
    public function checkFieldDisable()
    {
        if ($this->canDisplayUseDefault() && $this->usedDefault()) {
            $this->getElement()->setDisabled(true);
        }
        return $this;
    }

    /**
     * @return \Amasty\ShopbyBase\Model\OptionSetting
     */
    public function getDataObject()
    {
        return $this->getElement()->getForm()->getDataObject();
    }

    /**
     * @return bool
     */
    public function canDisplayUseDefault()
    {
        return (bool)$this->getDataObject()->getCurrentStoreId()
            || in_array($this->getElement()->getName(), ['meta_title', 'title']);
    }
}
