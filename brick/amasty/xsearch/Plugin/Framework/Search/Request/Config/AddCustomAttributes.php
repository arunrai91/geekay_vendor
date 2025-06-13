<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Plugin\Framework\Search\Request\Config;

use Amasty\Xsearch\Model\Config;
use Amasty\Xsearch\Model\CustomSearchAttributes;
use Magento\Framework\Search\Request\Config\FilesystemReader;
use Magento\Framework\Serialize\SerializerInterface;

class AddCustomAttributes
{
    /**
     * @var CustomSearchAttributes
     */
    private $customSearchAttributes;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(
        CustomSearchAttributes $customSearchAttributes,
        Config $config,
        SerializerInterface $serializer
    ) {
        $this->customSearchAttributes = $customSearchAttributes;
        $this->config = $config;
        $this->serializer = $serializer;
    }

    /**
     * @param FilesystemReader $subject
     * @param array $result
     * @param string|null $scope
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRead(FilesystemReader $subject, array $result, $scope = null): array
    {
        if (!($searchAttributesJson = $this->config->getSearchAttributesJson())
            || !($attributeConfig = $this->customSearchAttributes->getAttributes())
        ) {
            return $result;
        }
        $searchAttributes = $this->serializer->unserialize($searchAttributesJson);
        foreach ($attributeConfig as $code => $item) {
            if (isset($searchAttributes[$code])) {
                $result['quick_search_container']['queries']['search']['match'][] = [
                    'field' => $code,
                    'boost' => $searchAttributes[$code]
                ];
            }
        }

        return $result;
    }
}
