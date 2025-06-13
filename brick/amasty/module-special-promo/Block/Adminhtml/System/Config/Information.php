<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\Manager;
use Magento\Framework\View\Helper\Js;

/**
 * Amasty module information block.
 */
class Information extends Fieldset
{
    /**
     * @var string
     */
    private $userGuide = 'https://amasty.com/docs/doku.php?id=magento_2%3Aspecial-promotions';

    /**
     * @var string
     */
    private $suggestLink = 'https://amasty.com/docs/doku.php?id=magento_2:special-promotions&utm_source=extension' .
    '&utm_medium=backend&utm_campaign=suggest_spp#additional_packages_provided_in_composer_suggestions';

    /**
     * @var array
     */
    private $enemyExtensions = [];

    /**
     * @var string
     */
    private $content;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var string[]
     */
    private $suggestModules;

    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        array $data = [],
        ?Manager $moduleManager = null, // TODO move to not optional
        ?ProductMetadataInterface $productMetadata = null, // TODO move to not optional
        array $suggestModules = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->moduleManager = $moduleManager ?? ObjectManager::getInstance()->get(Manager::class);
        $this->productMetadata = $productMetadata ?? ObjectManager::getInstance()->get(ProductMetadataInterface::class);
        $this->suggestModules = $suggestModules;
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

    /**
     * @return array|string
     */
    public function getAdditionalModuleContent()
    {
        $result = [];

        if (!$this->moduleManager->isEnabled('Amasty_Mage2.4.7Fix')
            && $this->productMetadata->getVersion() === '2.4.7'
        ) {
            $result[] = [
                'type' => 'message-notice',
                'text' => __('Enable the module-mage-2.4.7-fix module for the extension to function correctly. '
                    .'Please, run the following command in the SSH: composer require amasty/module-mage-2.4.7-fix')
            ];
        }

        if ($this->shouldAddSuggestNotification()) {
            $result[] = [
                'type' => 'message-notice',
                'text' => __(
                    'Extra features may be provided by additional packages in the extension\'s \'suggest\' ' .
                    'section. Please explore the available suggested packages <a href="%1" target="_blank">here</a>.',
                    $this->suggestLink
                )
            ];
        }

        return $result ?: '';
    }

    private function shouldAddSuggestNotification(): bool
    {
        foreach ($this->suggestModules as $module) {
            if (!$this->moduleManager->isEnabled($module)) {
                return true;
            }
        }

        return false;
    }
}
