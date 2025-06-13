<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Sorting for Magento 2
 */

namespace Amasty\Sorting\Model\Source;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\Manager;

/**
 * @deprecated
 * @since 2.14.1
 * @see \Amasty\Base\Block\Adminhtml\System\Config\Form\Field\Promo\PromoField
 */
class BestsellersMultiStore extends Field
{
    /**
     * @var Manager
     */
    private $moduleManager;

    public function __construct(
        Context $context,
        Manager $moduleManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->moduleManager = $moduleManager;
    }

    protected function _getElementHtml(AbstractElement $element): string
    {
        if (!$this->moduleManager->isEnabled('Amasty_ImprovedSortingSubscriptionFunctionality')) {
            $element->setData('disabled', 'disabled');
            $element->setData(
                'comment',
                'The functionality is available as part of an active product subscription or support subscription.' .
                ' To upgrade and obtain functionality please follow the ' .
                '<a href="https://amasty.com/amcustomer/account/products/' .
                '?utm_source=extension&utm_medium=backend&utm_campaign=upgrade_sorting">link</a>.'
            );
        }

        return parent::_getElementHtml($element);
    }
}
