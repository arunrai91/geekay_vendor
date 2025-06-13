<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Plugin\ElasticSearch\Model\QuerySettingsProcessor;

use Amasty\ElasticSearch\Model\Config\QuerySettings;
use Amasty\ElasticSearch\Model\Config\QuerySettingsProcessor;
use Amasty\Xsearch\Model\CustomSearchAttributes;

class AddCustomAttributeConfig
{
    /**
     * @var CustomSearchAttributes
     */
    private $customSearchAttributes;

    public function __construct(CustomSearchAttributes $customSearchAttributes)
    {
        $this->customSearchAttributes = $customSearchAttributes;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetConfigValue(QuerySettingsProcessor $subject, ?array $result, string $attributeCode): ?array
    {
        if ($result === null) {
            $attributes = $this->customSearchAttributes->getAttributes();
            if (isset($attributes[$attributeCode])) {
                $attributeConfig = $attributes[$attributeCode];
                $result = [
                    QuerySettings::WILDCARD => $attributeConfig[QuerySettings::WILDCARD],
                    QuerySettings::SPELLING => $attributeConfig[QuerySettings::SPELLING],
                    QuerySettings::COMBINING => $attributeConfig[QuerySettings::COMBINING],
                ];
            }
        }

        return $result;
    }
}
