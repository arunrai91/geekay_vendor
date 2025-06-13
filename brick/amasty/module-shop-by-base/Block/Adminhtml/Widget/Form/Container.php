<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Block\Adminhtml\Widget\Form;

use Magento\Backend\Block\Widget\Container as WidgetContainer;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class Container extends WidgetContainer
{
    public const PARAM_BLOCK_GROUP = 'block_group';
    public const PARAM_MODE = 'mode';

    /**
     * @var string
     */
    private string $objectId = 'id';

    /**
     * @var string[]
     */
    private array $formScripts = [];

    /**
     * @var string[]
     */
    private array $formInitScripts = [];

    /**
     * @var string
     */
    private string $mode = 'edit';

    /**
     * @var string
     */
    private string $blockGroup = 'Magento_Backend';

    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/form/container.phtml';

    /**
     * @var SecureHtmlRenderer
     */
    private SecureHtmlRenderer $secureRenderer;

    public function __construct(
        SecureHtmlRenderer $secureRenderer,
        Context $context,
        array $data = []
    ) {
        $this->secureRenderer = $secureRenderer;
        parent::__construct($context, $data);
    }

    /**
     * Initialize form.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        if ($this->hasData(self::PARAM_BLOCK_GROUP)) {
            $this->blockGroup = $this->_getData(self::PARAM_BLOCK_GROUP);
        }
        if ($this->hasData(self::PARAM_MODE)) {
            $this->mode = $this->_getData(self::PARAM_MODE);
        }

        $this->addButton(
            'back',
            [
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $this->getBackUrl() . '\')',
                'class' => 'back'
            ],
            -1
        );
        $this->addButton(
            'reset',
            ['label' => __('Reset'), 'onclick' => 'setLocation(window.location.href)', 'class' => 'reset'],
            -1
        );

        $objId = (int)$this->getRequest()->getParam($this->objectId);

        if (!empty($objId)) {
            $this->addButton(
                'delete',
                [
                    'label' => __('Delete'),
                    'class' => 'delete',
                    'onclick' => 'deleteConfirm(\'' . __(
                        'Are you sure you want to do this?'
                    ) . '\', \'' . $this->getDeleteUrl() . '\', {data: {}})'
                ]
            );
        }

        $this->addButton(
            'save',
            [
                'label' => __('Save'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save', 'target' => '#edit_form']],
                ]
            ],
            1
        );
    }

    /**
     * Create form block
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        if ($this->blockGroup && $this->_controller && $this->mode && !$this->_layout->getChildName(
            $this->_nameInLayout,
            'form'
        )) {
            $this->addChild('form', $this->_buildFormClassName());
        }
        return parent::_prepareLayout();
    }

    public function _buildFormClassName(): string
    {
        return $this->nameBuilder->buildClassName(
            [$this->blockGroup, 'Block', $this->_controller, $this->mode, 'Form']
        );
    }

    public function getBackUrl(): string
    {
        return $this->getUrl('*/*/');
    }

    public function getDeleteUrl(): string
    {
        return $this->getUrl('*/*/delete', [$this->objectId => (int)$this->getRequest()->getParam($this->objectId)]);
    }

    public function getSaveUrl(): string
    {
        return $this->getFormActionUrl();
    }

    /**
     * Get form action URL
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        if ($this->hasFormActionUrl()) {
            return $this->getData('form_action_url');
        }
        return $this->getUrl('*/*/save');
    }

    /**
     * Get form HTML.
     *
     * @return string
     */
    public function getFormHtml()
    {
        $this->getChildBlock('form')->setData('action', $this->getSaveUrl());
        return $this->getChildHtml('form');
    }

    /**
     * Get form init scripts.
     *
     * @return string
     */
    public function getFormInitScripts(): string
    {
        if (count($this->formInitScripts)) {
            return $this->secureRenderer->renderTag(
                'script',
                [],
                implode("\n", $this->formInitScripts),
                false
            );
        }

        return '';
    }

    /**
     * Get form scripts.
     *
     * @return string
     */
    public function getFormScripts(): string
    {
        if (count($this->formScripts)) {
            return $this->secureRenderer->renderTag(
                'script',
                [],
                implode("\n", $this->formScripts),
                false
            );
        }

        return '';
    }

    public function getHeaderWidth(): string
    {
        return '';
    }

    public function getHeaderCssClass(): string
    {
        return 'icon-head head-' . strtr($this->_controller, '_', '-');
    }

    public function getHeaderHtml(): string
    {
        return '<h3 class="' . $this->getHeaderCssClass() . '">' . $this->getHeaderText() . '</h3>';
    }

    /**
     * Set data object and pass it to form
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function setDataObject($object): Container
    {
        $this->getChildBlock('form')->setDataObject($object);
        return $this->setData('data_object', $object);
    }

    public function setObjectId(string $objectId): void
    {
        $this->objectId = $objectId;
    }

    public function setBlockGroup(string $blockGroup): void
    {
        $this->blockGroup = $blockGroup;
    }
}
