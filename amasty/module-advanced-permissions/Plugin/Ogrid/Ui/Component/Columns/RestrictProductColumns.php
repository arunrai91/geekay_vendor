<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Plugin\Ogrid\Ui\Component\Columns;

use Amasty\Ogrid\Ui\Component\Columns;
use Amasty\Rolepermissions\Helper\Data as Helper;
use Amasty\Rolepermissions\Plugin\Ogrid\Helper\Data\RestrictOrderItemFields;

class RestrictProductColumns
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var bool
     */
    private $processed = false;

    public function __construct(
        Helper $helper
    ) {
        $this->helper = $helper;
    }

    public function afterPrepare(
        Columns $subject
    ): void {
        if (!$this->processed) {
            if ($restrictedAttributeCodes = $this->helper->getRestrictedAttributeCodes()) {
                $columnsConfiguration = $subject->getData('config');

                if (array_key_exists('productColsData', $columnsConfiguration)) {
                    foreach ($columnsConfiguration['productColsData'] as $id => $config) {
                        $fieldAttribute = str_replace(RestrictOrderItemFields::ORDER_ITEM_FIELD_PREFIX, '', $id);

                        if (in_array($fieldAttribute, $restrictedAttributeCodes)) {
                            unset($columnsConfiguration['productColsData'][$id]);
                        }
                    }

                    $subject->setData('config', $columnsConfiguration);
                }
            }

            $this->processed = true;
        }
    }
}
