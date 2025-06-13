<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Page for Magento 2 (System)
 */

namespace Amasty\ShopbyPage\Block\Adminhtml\Page\Edit;

class Form extends \Amasty\ShopbyBase\Block\Adminhtml\Widget\Form
{
    public function prepareForm(): Form
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->getDataFormFactory()->create(
            ['data' => [
                'id' => 'edit_form',
                'action' => $this->getData('action'),
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            ]]
        );
        $form->setUseContainer(true);

        $this->setForm($form);

        return parent::prepareForm();
    }
}
