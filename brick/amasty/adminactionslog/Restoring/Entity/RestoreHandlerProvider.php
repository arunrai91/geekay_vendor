<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Restoring\Entity;

use Amasty\AdminActionsLog\Api\Restoring\EntityRestoreHandlerInterface;
use Amasty\AdminActionsLog\Logging\Util\ClassNameNormalizer;
use Magento\Framework\ObjectManagerInterface;

class RestoreHandlerProvider
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
     * @var RestoreHandler\Common
     */
    private $commonRestoreHandler;

    /**
     * @var array
     */
    private $entityTypes;

    public function __construct(
        ObjectManagerInterface $objectManager,
        ClassNameNormalizer $classNameNormalizer,
        RestoreHandler\Common $commonRestoreHandler,
        array $entityTypes = []
    ) {
        foreach ($entityTypes as $entityTypeClass => &$affectedObjectClasses) {
            if (!is_subclass_of($entityTypeClass, EntityRestoreHandlerInterface::class)) {
                throw new \LogicException(
                    sprintf(
                        'EntityRestoreHandler "%s" must implement %s',
                        $entityTypeClass,
                        EntityRestoreHandlerInterface::class
                    )
                );
            }

            $affectedObjectClasses = array_map(function ($affectedClass) {
                return trim($affectedClass, '\\');
            }, $affectedObjectClasses);
        }

        $this->objectManager = $objectManager;
        $this->classNameNormalizer = $classNameNormalizer;
        $this->commonRestoreHandler = $commonRestoreHandler;
        $this->entityTypes = $entityTypes;
    }

    public function get(string $objectClass): EntityRestoreHandlerInterface
    {
        $objectClass = $this->classNameNormalizer->execute($objectClass);

        foreach ($this->entityTypes as $entityTypeClass => $affectedObjectClasses) {
            if (in_array($objectClass, $affectedObjectClasses)) {
                return $this->objectManager->get($entityTypeClass);
            }
        }

        return $this->commonRestoreHandler;
    }
}
