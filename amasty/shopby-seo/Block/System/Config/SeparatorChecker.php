<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Block\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;

class SeparatorChecker extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var string
     */
    private $userGuide = 'https://amasty.com/docs/doku.php?id=magento_2:improved_layered_navigation#seo_settings';

    /**
     * @var string
     */
    protected $_template = 'Amasty_ShopbySeo::system/config/checker.phtml';

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element = clone $element;
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * @return string
     */
    public function getUserGuideUrl()
    {
        return $this->userGuide;
    }
}
