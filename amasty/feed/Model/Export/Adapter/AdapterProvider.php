<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Export\Adapter;

use Magento\Framework\Exception\LocalizedException;
use Magento\ImportExport\Model\Export\Adapter\AbstractAdapter;

class AdapterProvider
{
    /**
     * @var DocumentFactory[]
     */
    private $adapterFactories;

    public function __construct($adapters)
    {
        $this->adapterFactories = $adapters;
    }

    /**
     * @throws LocalizedException
     */
    public function get(string $adapterName, array $params): AbstractAdapter
    {
        if (!isset($this->adapterFactories[$adapterName])) {
            throw new LocalizedException(__('Please correct the file format.'));
        }

        return $this->adapterFactories[$adapterName]->create($params);
    }
}
