<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Block\Adminhtml\System\Config;

use Amasty\ElasticSearch\Model\Config;
use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Helper\Js;

class CatalogGroup extends Fieldset
{
    private const ALLOWED_ENGINES_SHOW_CATALOG_GROUP = [
        'amasty_elastic' => 'amasty_elastic',
        'amasty_elastic_8' => 'amasty_elastic_8',
        'amasty_elastic_opensearch' => 'amasty_elastic_opensearch',
    ];

    /**
     * @var string[]
     */
    private array $allowedEnginesShowCatalogGroup;

    /**
     * @var Config
     */
    private Config $config;

    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        Config $config,
        array $allowedEnginesShowCatalogGroup = [],
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);

        $this->config = $config;
        $this->allowedEnginesShowCatalogGroup
            = array_merge($allowedEnginesShowCatalogGroup, self::ALLOWED_ENGINES_SHOW_CATALOG_GROUP);
    }

    public function render(AbstractElement $element): string
    {
        $showCatalogGroupScript =  $this->getLayout()
            ->createBlock(Template::class)
            ->setData('is_engine_valid', $this->isEngineValid())
            ->setTemplate('Amasty_ElasticSearch::system/config/catalog-group.phtml');

        return parent::render($element) . $showCatalogGroupScript->toHtml();
    }

    private function isEngineValid(): bool
    {
        return in_array($this->config->getEngine(), $this->allowedEnginesShowCatalogGroup);
    }
}
