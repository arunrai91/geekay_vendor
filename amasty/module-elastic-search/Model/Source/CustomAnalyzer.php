<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Source;

use Amasty\ElasticSearch\Model\Indexer\Structure\CustomAnalyzersMetaInfoProvider;

class CustomAnalyzer implements \Magento\Framework\Data\OptionSourceInterface
{
    public const DISABLED = 'disabled';
    public const CHINESE = 'smartcn';
    public const JAPANESE = 'kuromoji';
    public const KOREAN = 'nori';

    /**
     * @var CustomAnalyzersMetaInfoProvider
     */
    private $metaInfoProvider;

    public function __construct(
        CustomAnalyzersMetaInfoProvider $customAnalyzersMetaInfoProvider
    ) {
        $this->metaInfoProvider = $customAnalyzersMetaInfoProvider;
    }

    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        $customAnalyzers = array_map(function ($alias) {
            return [
                'value' => $alias,
                'label' => $this->metaInfoProvider->getAnalyzerLabel($alias)
            ];
        }, $this->metaInfoProvider->getAllAnalyzers());
        array_unshift($customAnalyzers, ['value' => self::DISABLED, 'label' => __('Disabled')]);

        return $customAnalyzers;
    }
}
