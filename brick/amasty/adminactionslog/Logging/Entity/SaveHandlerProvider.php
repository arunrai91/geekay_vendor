<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\Entity;

use Amasty\AdminActionsLog\Api\Logging\EntitySaveHandlerInterface;
use Amasty\AdminActionsLog\Logging\Util\ClassNameNormalizer;
use Magento\Framework\ObjectManagerInterface;

class SaveHandlerProvider
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ClassNameNormalizer
     */
    private $classNameNormalizer;

    /**
     * @var SaveHandler\Common
     */
    private $commonSaveHandler;

    /**
     * @var array
     */
    private $entityTypes;

    public function __construct(
        ObjectManagerInterface $objectManager,
        ClassNameNormalizer $classNameNormalizer,
        SaveHandler\Common $commonSaveHandler,
        array $entityTypes = []
    ) {
        foreach ($entityTypes as $entityTypeClass => &$affectedObjectClasses) {
            if (!is_subclass_of($entityTypeClass, EntitySaveHandlerInterface::class)) {
                throw new \LogicException(
                    sprintf(
                        'EntitySaveHandler "%s" must implement %s',
                        $entityTypeClass,
                        EntitySaveHandlerInterface::class
                    )
                );
            }

            $affectedObjectClasses = array_map(function ($affectedClass) {
                return trim($affectedClass, '\\');
            }, $affectedObjectClasses);
        }

        $this->objectManager = $objectManager;
        $this->classNameNormalizer = $classNameNormalizer;
        $this->commonSaveHandler = $commonSaveHandler;
        $this->entityTypes = $entityTypes;
    }

    public function get(string $objectClass): EntitySaveHandlerInterface
    {
        $objectClass = $this->classNameNormalizer->execute($objectClass);

        foreach ($this->entityTypes as $entityTypeClass => $affectedObjectClasses) {
            foreach ($affectedObjectClasses as $affectedObjectClass) {
                if ($objectClass === $affectedObjectClass || \is_subclass_of($objectClass, $affectedObjectClass)) {
                    return $this->objectManager->get($entityTypeClass);
                }
            }
        }

        return $this->commonSaveHandler;
    }
}
