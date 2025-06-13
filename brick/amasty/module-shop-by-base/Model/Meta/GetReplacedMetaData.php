<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\Meta;

use Amasty\ShopbyBase\Model\Di\Wrapper;

class GetReplacedMetaData
{
    public const META_TITLE_IDENTIFIER = 'meta_title';
    public const META_DESCRIPTION_IDENTIFIER = 'meta_description';
    public const META_KEYWORDS_IDENTIFIER = 'meta_keywords';

    /**
     * @var \Amasty\Meta\Model\Meta\ReplacedData
     */
    public $replacedMetaData;

    public function __construct(
        Wrapper $replacedMetaData
    ) {
        $this->replacedMetaData = $replacedMetaData;
    }

    /**
     * Getting replaced meta data from Amasty_Meta module
     *
     * @param string $identifier
     * @return string|null
     */
    public function execute(string $identifier): ?string
    {
        $value = null;

        if ($replacedData = $this->replacedMetaData->getReplacedData()) {
            $value = $replacedData[$identifier] ?? null;
        }

        return $value;
    }
}
