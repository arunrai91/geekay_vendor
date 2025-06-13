<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Block\Adminhtml\System\Config\Form;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Email extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param AbstractElement $element
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $js = '<script type="text/javascript">
            require([
                "Magento_Ui/js/lib/view/utils/async",
                "Amasty_AdminActionsLog/js/form/tag-it"
            ], function ($) {
                "use strict";

                var input = "#' . $element->getHtmlId() . '";
                $.async(input, (function() {
                    $(input).tagit();
                }));
            });
            </script>';
        $element->setAfterElementJs($js);
        $element->setData('class', "amactionslog-input");

        return parent::_getElementHtml($element);
    }
}
