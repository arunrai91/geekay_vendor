<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */
namespace Amasty\Shopby\Block\Navigation;

use Amasty\ShopbyBase\Api\UrlBuilderInterface;
use Magento\Framework\View\Element\Template;

/**
 * @api
 */
class UrlModifier extends \Magento\Framework\View\Element\Template
{
    public const VAR_REPLACE_URL = 'amasty_shopby_replace_url';

    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'navigation/url_modifier.phtml';

    /**
     * @var UrlBuilderInterface
     */
    private UrlBuilderInterface $amUrlBuilder;

    public function __construct(
        Template\Context $context,
        UrlBuilderInterface $urlBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->amUrlBuilder = $urlBuilder;
    }

    public function getCurrentUrl()
    {
        $filterState = [];

        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_query'] = $filterState;
        $params['_escape'] = true;
        return str_replace('&amp;', '&', $this->amUrlBuilder->getUrl('*/*/*', $params));
    }

    public function replaceUrl()
    {
        return $this->getRequest()->getParam(\Amasty\Shopby\Block\Navigation\UrlModifier::VAR_REPLACE_URL) !== null;
    }
}
