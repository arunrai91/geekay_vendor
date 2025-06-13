<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Model\Config\Backend;

use Amasty\Base\Model\SysInfo\RegisteredInstanceRepository;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

class SystemInstanceKey extends Value
{
    /**
     * @var RegisteredInstanceRepository
     */
    private $registeredInstanceRepository;

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        RegisteredInstanceRepository $registeredInstanceRepository
    ) {
        $this->registeredInstanceRepository = $registeredInstanceRepository;
        parent::__construct($context, $registry, $config, $cacheTypeList);
    }

    /**
     * @return SystemInstanceKey
     */
    public function beforeSave()
    {
        // prevent saving instance key in config
        $this->setValue('');

        return parent::beforeSave();
    }

    /**
     * @return SystemInstanceKey
     */
    public function afterLoad()
    {
        if ($systemInstanceKey = $this->getSystemInstanceKey()) {
            $this->setValue($systemInstanceKey);
        }

        return parent::afterLoad();
    }

    private function getSystemInstanceKey(): ?string
    {
        $registeredInstance = $this->registeredInstanceRepository->get()->getCurrentInstance();

        return $registeredInstance
            ? $registeredInstance->getSystemInstanceKey()
            : null;
    }
}
