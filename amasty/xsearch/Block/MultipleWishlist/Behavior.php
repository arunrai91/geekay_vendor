<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Block\MultipleWishlist;

use Amasty\Xsearch\Model\Di\Wrapper;
use Magento\Framework\View\Element\Template;

class Behavior extends Template
{
    /**
     * @var Wrapper
     */
    private $wrapper;

    public function __construct(Template\Context $context, Wrapper $wrapper, array $data = [])
    {
        parent::__construct($context, $data);
        $this->wrapper = $wrapper;
    }

    protected function _toHtml()
    {
        return class_exists(\Magento\MultipleWishlist\Block\Behaviour::class)
            ? $this->wrapper->setTemplate('Magento_MultipleWishlist::behaviour.phtml')->toHtml()
            : '';
    }
}
