<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Plugin\Backend\Model\Config;

use Amasty\Base\Block\Adminhtml\System\Config\Information;
use Magento\Config\Model\Config\ScopeDefiner;
use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\Element\Section;
use Magento\Config\Model\Config\StructureElementInterface;

class AddInformationBlockPlugin
{
    /**
     * @var ScopeDefiner
     */
    private $scopeDefiner;

    public function __construct(
        ScopeDefiner $scopeDefiner
    ) {
        $this->scopeDefiner = $scopeDefiner;
    }

    /**
     * @param Structure $subject
     * @param Section $result
     * @return StructureElementInterface
     */
    public function afterGetElementByPathParts(
        Structure $subject,
        StructureElementInterface $result
    ): StructureElementInterface {
        if (!$result->getAttribute('tab')
            || $result->getAttribute('tab') !== StructurePlugin::AMASTY_TAB_NAME
            || !$result->getAttribute('resource')
        ) {
            return $result;
        }
        $moduleChildes = $result->getAttribute('children');
        if (isset($moduleChildes['amasty_information'])) {
            return $result; //backward compatibility
        }
        $moduleCode = strtok($result->getAttribute('resource'), '::');
        $moduleChildes =
            [
                'amasty_information' => [
                    'id' => 'amasty_information',
                    'translate' => 'label',
                    'type' => 'text',
                    'sortOrder' => '1',
                    'showInDefault' => '1',
                    'showInWebsite' => '1',
                    'showInStore' => '1',
                    'label' => 'Information',
                    'frontend_model' => Information::class,
                    '_elementType' => 'group',
                    'path' => $result->getAttribute('id') ?? '',
                    'module_code' => $moduleCode
                ]
            ] + $moduleChildes;
        $result->getChildren()->setElements($moduleChildes, $this->scopeDefiner->getScope());

        return $result;
    }
}
