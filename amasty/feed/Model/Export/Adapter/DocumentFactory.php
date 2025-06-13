<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Export\Adapter;

use Magento\Framework\ObjectManagerInterface;
use Magento\ImportExport\Model\Export\Adapter\AbstractAdapter;

class DocumentFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $instanceName;

    public function __construct(
        ObjectManagerInterface $objectManager,
        string $instanceName = ''
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    public function create(array $data = []): AbstractAdapter
    {
        return $this->objectManager->create($this->instanceName, $data);
    }
}
