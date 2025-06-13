<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2019-10-09T13:26:34+00:00
 * File:          Ui/Plugin/Component/MassActionPlugin.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Ui\Plugin\Component;

use Magento\Ui\Component\MassAction;

/**
 * Class MassActionPlugin
 * @package Xtento\OrderExport\Ui\Plugin\Component
 */
class MassActionPlugin
{
    /**
     * @var \Xtento\OrderExport\Helper\Module
     */
    protected $moduleHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * Adminhtml data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $adminhtmlData = null;

    /**
     * @var \Xtento\OrderExport\Model\System\Config\Source\Export\ProfileFactory
     */
    protected $profileFactory;

    /**
     * @var \Xtento\OrderExport\Helper\Entity
     */
    protected $entityHelper;

    /**
     * MassActionPlugin constructor.
     * @param \Xtento\OrderExport\Helper\Module $moduleHelper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     * @param \Xtento\OrderExport\Model\System\Config\Source\Export\ProfileFactory $profileFactory
     * @param \Xtento\OrderExport\Helper\Entity $entityHelper
     */
    public function __construct(
        \Xtento\OrderExport\Helper\Module $moduleHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Backend\Helper\Data $adminhtmlData,
        \Xtento\OrderExport\Model\System\Config\Source\Export\ProfileFactory $profileFactory,
        \Xtento\OrderExport\Helper\Entity $entityHelper
    ) {
        $this->moduleHelper = $moduleHelper;
        $this->request = $request;
        $this->scopeConfig = $config;
        $this->registry = $registry;
        $this->authorization = $authorization;
        $this->adminhtmlData = $adminhtmlData;
        $this->profileFactory = $profileFactory;
        $this->entityHelper = $entityHelper;
    }

    /**
     * Add massactions to the Sales > Orders grid.
     * Why not via sales_order_grid.xml? Because then you cannot select the actions which should be shown from
     * the Magento admin, this is required so admins can adjust the actions via the configuration.
     *
     * @param MassAction $subject
     * @param string $interceptedOutput
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    // @codingStandardsIgnoreStart
    public function afterPrepare(MassAction $subject, $interceptedOutput)
    {
        // @codingStandardsIgnoreEnd
        $gridId = $subject->getContext()->getNamespace();
        if (!in_array($gridId, $this->getGridIdentifiers())) {
            return;
        }
        if (!$this->moduleHelper->isModuleEnabled()) {
            return;
        }
        if ($this->registry->registry('xtDisabled') !== false) {
            return;
        }
        if (!$this->authorization->isAllowed('Xtento_OrderExport::manual')) {
            return;
        }
        $dataProvider = $subject->getContext()->getDataProvider()->getName(); // E.g.: sales_order_invoice_grid_data_source
        preg_match('/sales\_(.*)\_grid/', $dataProvider, $dataProviderMatches);
        if (isset($dataProviderMatches[1]) && !empty($dataProviderMatches[1])) {
            $entity = str_replace('order_', '', $dataProviderMatches[1]);
        } else {
            return;
        }

        $config = $subject->getData('config');

        if (!isset($config['component']) || strstr($config['component'], 'tree') === false) {
            // Temporary until added to core to support multi-level selects
            $config['component'] = 'Magento_Ui/js/grid/tree-massactions';
        }

        if (!isset($config['actions'])) {
            $config['actions'] = [];
        }
        $config['actions'] = $this->addExportAction($subject, $config['actions'], $entity);

        $subject->setData('config', $config);
    }

    protected function addExportAction($subject, $configActions, $entity)
    {
        $subActions = [];
        $exportProfiles = $this->profileFactory->create()->toOptionArray(false, $entity);
        foreach ($exportProfiles as $exportProfile) {
            $subActions[] = [
                'type' => 'profile_' . $exportProfile['value'],
                'label' => __('Profile: %1', $exportProfile['label']),
                'url' => $this->adminhtmlData->getUrl(
                    'xtento_orderexport/manual/gridPost',
                    [
                        'type' => $entity,
                        'profile_id' => $exportProfile['value'],
                        'namespace' => $subject->getContext()->getNamespace()
                    ]
                )
            ];
        }

        $configActions[] = [
            'type' => 'xtento_' . $entity . '_export',
            'label' => __('Export %1', $this->entityHelper->getPluralEntityName($entity)),
            'actions' => $subActions
        ];

        return $configActions;
    }

    /*
     * Get controller names where the module is supposed to modify the block
     */
    protected function getGridIdentifiers($entity = false)
    {
        $gridIdentifiers = [];
        if (!$entity || $entity == \Xtento\OrderExport\Model\Export::ENTITY_ORDER) {
            array_push($gridIdentifiers, 'sales_order_grid');
        }
        if (!$entity || $entity == \Xtento\OrderExport\Model\Export::ENTITY_INVOICE) {
            array_push($gridIdentifiers, 'sales_order_invoice_grid');
        }
        if (!$entity || $entity == \Xtento\OrderExport\Model\Export::ENTITY_SHIPMENT) {
            array_push($gridIdentifiers, 'sales_order_shipment_grid');
        }
        if (!$entity || $entity == \Xtento\OrderExport\Model\Export::ENTITY_CREDITMEMO) {
            array_push($gridIdentifiers, 'sales_order_creditmemo_grid');
        }
        return $gridIdentifiers;
    }
}
