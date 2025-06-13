<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Ui\DataProvider\Product\Form\Modifier;

use Amasty\Rolepermissions\Helper\Data;
use Amasty\Rolepermissions\Model\Entity\Attribute\Source\Admins;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Stdlib\ArrayManager;

class Owner extends AbstractModifier
{
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var Admins
     */
    private $admins;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var Data
     */
    protected $helper;

    public function __construct(
        ArrayManager $arrayManager,
        Admins $admins,
        AuthorizationInterface $authorization,
        Data $helper
    ) {
        $this->arrayManager = $arrayManager;
        $this->admins = $admins;
        $this->authorization = $authorization;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        foreach ($meta as $sectionName => &$section) {
            if (isset($section['children']['container_amrolepermissions_owner'])) {
                if (!$this->authorization->isAllowed('Amasty_Rolepermissions::product_owner')) {
                    $meta = $this->arrayManager->remove(
                        "$sectionName/children/container_amrolepermissions_owner",
                        $meta
                    );
                    break;
                } else {
                    $owner['arguments']['data']['config'] = [
                        'formElement' => 'select',
                        'dataType' => 'select',
                        'options' => $this->admins->getAllOptions()
                    ];

                    $meta = $this->arrayManager->merge(
                        "$sectionName/children/container_amrolepermissions_owner/children/amrolepermissions_owner",
                        $meta,
                        $owner
                    );
                    break;
                }
            }
        }

        $model = $this->helper->currentRule();

        if ($model
            && $model->getLimitProductSourcesManagement()
            && isset($meta['sources'])
        ) {
            $meta['sources']['arguments']['data']['config']['visible'] = false;
        }

        return $meta;
    }
}
