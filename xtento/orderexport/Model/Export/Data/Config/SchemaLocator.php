<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2017-11-27T20:02:26+00:00
 * File:          Model/Export/Data/Config/SchemaLocator.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Data\Config;

use Magento\Framework\Module\Dir;

class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for merged config
     *
     * @var string
     */
    protected $_schema;

    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     */
    public function __construct(\Magento\Framework\Module\Dir\Reader $moduleReader)
    {
        $this->_schema = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Xtento_OrderExport') . '/' . 'xtento/orderexport_data.xsd';
    }

    /**
     * {@inheritdoc}
     */
    public function getSchema()
    {
        return $this->_schema;
    }

    /**
     * {@inheritdoc}
     */
    public function getPerFileSchema()
    {
        return null;
    }
}
