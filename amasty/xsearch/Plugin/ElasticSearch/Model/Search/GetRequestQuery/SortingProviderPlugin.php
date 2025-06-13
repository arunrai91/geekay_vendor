<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Plugin\ElasticSearch\Model\Search\GetRequestQuery;

use Amasty\Xsearch\Model\Config;
use Magento\Framework\App\RequestInterface;

class SortingProviderPlugin
{
    public const FIELD = 'stock_status';
    public const DIRECTION = 'asc';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var array
     */
    private $searchModules = [
        'catalogsearch',
        'amasty_xsearch'
    ];

    public function __construct(
        RequestInterface $request,
        Config $config
    ) {
        $this->config = $config;
        $this->request = $request;
    }

    /**
     * @param mixed $subject
     * @param array $result
     * @return array
     */
    public function afterGetRequestedSorting($subject, array $result): array
    {
        if ($this->isAvailable()) {
            array_unshift($result, ['field' => self::FIELD, 'direction' => self::DIRECTION]);
        }

        return $result;
    }

    /**
     * @param $subject
     * @param array $result
     * @return array
     */
    public function afterGetSort($subject, array $result): array
    {
        if ($this->isAvailable()) {
            array_unshift($result, [self::FIELD => ['order' => self::DIRECTION]]);
        }

        return $result;
    }

    protected function isAvailable(): bool
    {
        return in_array($this->request->getModuleName(), $this->searchModules)
            && $this->getConfig()->isShowOutOfStockLast();
    }

    public function getConfig(): Config
    {
        return $this->config;
    }
}
