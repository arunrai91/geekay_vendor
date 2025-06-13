<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Sorting for Magento 2
 */

namespace Amasty\Sorting\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\App\ProductMetadataInterface;

class Information extends Fieldset
{
    /**
     * @var string
     */
    private $userGuide = 'https://amasty.com/docs/doku.php?id=magento_2:improved-sorting';

    /**
     * @var array
     */
    private $enemyExtensions = [];

    /**
     * @var string
     */
    private $content;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = [],
        ProductMetadataInterface $productMetadata = null // TODO move to not optional
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->moduleManager = $moduleManager;
        $this->productMetadata = $productMetadata
            ?? ObjectManager::getInstance()->get(ProductMetadataInterface::class);
    }

    /**
     * Render fieldset html
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = $this->_getHeaderHtml($element);

        $this->setContent(__('Please update Amasty Base module. Re-upload it and replace all the files.'));

        $this->_eventManager->dispatch(
            'amasty_base_add_information_content',
            ['block' => $this]
        );

        $html .= $this->getContent();
        $html .= $this->_getFooterHtml($element);

        $html = str_replace(
            'amasty_information]" type="hidden" value="0"',
            'amasty_information]" type="hidden" value="1"',
            $html
        );
        $html = preg_replace('(onclick=\"Fieldset.toggleCollapse.*?\")', '', $html);

        return $html;
    }

    /**
     * @return array|string
     */
    public function getAdditionalModuleContent()
    {
        if ($this->moduleManager->isEnabled('Magento_GraphQl')
            && !$this->moduleManager->isEnabled('Amasty_SortingGraphQl')
        ) {
            $result[] = [
                'type' => 'message-notice',
                'text' => __('Enable improved-sorting-graphql module to '
                    . 'activate GraphQl and Sorting. '
                    . 'Please, run the following command in the SSH: '
                    . 'composer require amasty/improved-sorting-graphql')
            ];
        }

        if ($this->moduleManager->isEnabled('Yotpo_Yotpo')
            && !$this->moduleManager->isEnabled('Amasty_Yotpo')
        ) {
            $result[] = [
                'type' => 'message-notice',
                'text' => __('Enable amasty/yotpo module to '
                    . 'activate Yotpo and Sorting. '
                    . 'Please, run the following command in the SSH: '
                    . 'composer require amasty/yotpo')
            ];
        }

        if ($m245notice = $this->getM245Notice()) {
            $result[] = $m245notice;
        }

        return $result ?? '';
    }

    private function getM245Notice(): ?array
    {
        $notice = null;

        if (!$this->moduleManager->isEnabled('Amasty_Mage245Fix')
            && strpos($this->productMetadata->getVersion(), '2.4.5') !== false
        ) {
            $notice = [
                'type' => 'message-notice',
                'text' => __('Enable the module-mage-2.4.5-fix module for the extension to function correctly. '
                    . 'Please, run the following command in the SSH: composer require amasty/module-mage-2.4.5-fix')
            ];
        }

        if ($this->moduleManager->isEnabled('Amasty_Mage245Fix')
            && strpos($this->productMetadata->getVersion(), '2.4.5') === false
        ) {
            $notice = [
                'type' => 'message-notice',
                'text' => __('Considering your current Magento version, please disable the module-mage-2.4.5-fix '
                    . 'module to prevent frontend issues, as it is only compatible with version 2.4.5. '
                    . 'Please, run the following command in the SSH: php bin/magento module:disable Amasty_Mage245Fix')
            ];
        }

        return $notice;
    }

    /**
     * @return string
     */
    public function getUserGuide()
    {
        return $this->userGuide;
    }

    /**
     * @param string $userGuide
     */
    public function setUserGuide($userGuide)
    {
        $this->userGuide = $userGuide;
    }

    /**
     * @return array
     */
    public function getEnemyExtensions()
    {
        return $this->enemyExtensions;
    }

    /**
     * @param array $enemyExtensions
     */
    public function setEnemyExtensions($enemyExtensions)
    {
        $this->enemyExtensions = $enemyExtensions;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
}
