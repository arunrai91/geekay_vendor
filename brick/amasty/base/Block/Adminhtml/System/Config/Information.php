<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Block\Adminhtml\System\Config;

use Amasty\Base\Block\Adminhtml\System\Config\InformationBlocks\Basic;
use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\Js;
use Magento\Framework\View\LayoutFactory;

class Information extends Fieldset
{
    public const SEO_PARAMS = '?utm_source=extension&utm_medium=backend&utm_campaign=';

    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        LayoutFactory $layoutFactory,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->layoutFactory = $layoutFactory;
    }

    public function render(AbstractElement $element)
    {
        if (!($moduleCode = $element->getDataByPath('group/module_code'))
            || $moduleCode === 'Amasty_Base'
            || !($innerHtml = $this->getInnerHtml($moduleCode, $element))
        ) {
            return '';
        }

        $html = $this->_getHeaderHtml($element)
            . $innerHtml
            . $this->_getFooterHtml($element);
        $html = str_replace(
            'amasty_information]" type="hidden" value="0"',
            'amasty_information]" type="hidden" value="1"',
            $html
        );

        return preg_replace('(onclick=\"Fieldset.toggleCollapse.*?\")', '', $html);
    }

    private function getInnerHtml(string $moduleCode, AbstractElement $element): string
    {
        $html = '';

        $layout = $this->layoutFactory->create(['cacheable' => false]);
        $layout->getUpdate()->load(
            [
                'amasty_base_information_block',
                strtolower($moduleCode) . '_information_block'
            ]
        );
        $layout->generateXml();
        $layout->generateElements();

        /** @var Basic $basicBlock */
        $basicBlock = $layout->getBlock('aminfotab.basic');
        if ($basicBlock) {
            $basicBlock->setData('element', $element);
            $html .= $basicBlock->toHtml();
        }

        return $html;
    }
}
