<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2016-02-29T14:57:03+00:00
 * File:          Block/Adminhtml/Destination/Edit/Tab/Type/Webservice.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Block\Adminhtml\Destination\Edit\Tab\Type;

class Webservice extends AbstractType
{
    // Webservice Configuration
    public function getFields(\Magento\Framework\Data\Form $form)
    {
        $fieldset = $form->addFieldset('config_fieldset', [
            'legend' => __('Webservice Configuration'),
            'class' => 'fieldset-wide'
        ]
        );

        $fieldset->addField('webservice_note', 'note', [
            'text' => __('<b>Instructions</b>: To export data to a webservice, please follow the following steps:<br>1) Go into the <i>app/code/Xtento/OrderExport/Model/Destination/</i> directory and rename the file "Webservice.php.sample" to "Webservice.php"<br>2) Enter the function name you want to call in the Webservice.php class in the field below.<br>3) Open the Webservice.php file and add a function that matches the function name you entered. This function will be called by this destination upon exporting then.<br><br><b>Example:</b> If you enter server1 in the function name field below, a method called server1($fileArray) must exist in the Webservice.php file. This way multiple webservices can be added to the Webservice class, and can be called from different export destination, separated by the function name that is called. The function you add then gets called whenever this destination is executed by an export profile.')
        ]
        );

        $fieldset->addField('custom_function', 'text', [
            'label' => __('Custom Function'),
            'name' => 'custom_function',
            'note' => __('Please make sure the function you enter exists like this in the app/code/Xtento/OrderExport/Model/Destination/Webservice.php file:<br>public function <i>yourFunctionName</i>($fileArray) { ... }'),
            'required' => true
        ]
        );
    }
}