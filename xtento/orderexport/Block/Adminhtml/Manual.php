<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2018-12-02T14:57:13+00:00
 * File:          Block/Adminhtml/Manual.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Block\Adminhtml;

class Manual extends \Magento\Backend\Block\Template
{
    /**
     * @var \Xtento\OrderExport\Model\System\Config\Source\Export\Profile
     */
    protected $profileSource;

    /**
     * @var \Xtento\OrderExport\Helper\Entity
     */
    protected $entityHelper;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var \Magento\Framework\View\Element\Html\Date
     */
    protected $dateElement;

    /**
     * @var \Xtento\OrderExport\Model\ExportFactory
     */
    protected $exportFactory;

    /**
     * @var \Xtento\XtCore\Model\System\Config\Source\Order\AllStatuses
     */
    protected $allStatuses;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface
     */
    protected $dateTimeFormatter;

    /**
     * Manual constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Xtento\OrderExport\Model\System\Config\Source\Export\Profile $profileSource
     * @param \Xtento\OrderExport\Model\System\Config\Source\Export\Status $allStatuses
     * @param \Xtento\OrderExport\Helper\Entity $entityHelper
     * @param \Magento\Framework\View\Element\Html\Date $dateElement
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface $dateTimeFormatter
     * @param \Xtento\OrderExport\Model\ExportFactory $exportFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\System\Store $systemStore,
        \Xtento\OrderExport\Model\System\Config\Source\Export\Profile $profileSource,
        \Xtento\OrderExport\Model\System\Config\Source\Export\Status $allStatuses,
        \Xtento\OrderExport\Helper\Entity $entityHelper,
        \Magento\Framework\View\Element\Html\Date $dateElement,
        \Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface $dateTimeFormatter,
        \Xtento\OrderExport\Model\ExportFactory $exportFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->profileSource = $profileSource;
        $this->entityHelper = $entityHelper;
        $this->systemStore = $systemStore;
        $this->dateElement = $dateElement;
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->allStatuses = $allStatuses;
        $this->exportFactory = $exportFactory;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Xtento_OrderExport::manual_export.phtml');
    }

    public function getJs($filename)
    {
        $url = $this->_assetRepo->createAsset(
            'Xtento_OrderExport::js/' . $filename,
            ['_secure' => $this->getRequest()->isSecure()]
        )->getUrl();
        return $url;
    }

    public function getProfileSelectorHtml()
    {
        $html = '<select class="select" name="profile_id" id="profile_id" style="width: 320px;">';
        $html .= '<option value="">' . __('--- Select Profile---') . '</option>';
        $enabledProfiles = $this->profileSource->toOptionArray();
        $profilesByGroup = [];
        foreach ($enabledProfiles as $profile) {
            $profilesByGroup[$profile['entity']][] = $profile;
        }
        foreach ($profilesByGroup as $entity => $profiles) {
            $html .= '<optgroup label="' . __(
                    '%1 Export',
                    $this->entityHelper->getEntityName($entity)
                ) . '">';
            foreach ($profiles as $profile) {
                $html .= '<option value="' . $profile['value'] . '" entity="' . $entity . '">' . $profile['label'] . ' (' . __(
                        'ID: %1',
                        $profile['value']
                    ) . ')</option>';
            }
            $html .= '</optgroup>';
        }
        $html .= '</select>';
        return $html;
    }

    public function getStoreViewSelectorHtml()
    {
        $websiteCollection = $this->systemStore->getWebsiteCollection();
        $groupCollection = $this->systemStore->getGroupCollection();
        $storeCollection = $this->systemStore->getStoreCollection();

        $html = '<select multiple="multiple" id="store_id" name="store_id[]" style="width: 320px; height: 130px; margin-bottom: 10px;">';

        $html .= '<option value="0" selected="selected">' . __(
                'All Store Views'
            ) . '</option>';

        foreach ($websiteCollection as $website) {
            $websiteShow = false;
            foreach ($groupCollection as $group) {
                if ($group->getWebsiteId() != $website->getId()) {
                    continue;
                }
                $groupShow = false;
                foreach ($storeCollection as $store) {
                    if ($store->getGroupId() != $group->getId()) {
                        continue;
                    }
                    if (!$websiteShow) {
                        $websiteShow = true;
                        $html .= '<optgroup label="' . $website->getName() . '"></optgroup>';
                    }
                    if (!$groupShow) {
                        $groupShow = true;
                        $html .= '<optgroup label="&nbsp;&nbsp;&nbsp;&nbsp;' . $group->getName() . '">';
                    }
                    $html .= '<option value="' . $store->getId() . '">&nbsp;&nbsp;&nbsp;&nbsp;' . $store->getName(
                        ) . '</option>';
                }
                if ($groupShow) {
                    $html .= '</optgroup>';
                }
            }
        }
        $html .= '</select>';
        return $html;
    }

    public function getCalendarHtml($id)
    {
        $this->dateElement->setData(
            [
                'name' => $id,
                'id' => $id,
                'class' => '',
                'value' => '',
                'date_format' => $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT),
                'image' => $this->getViewFileUrl('Magento_Theme::calendar.png'),
            ]
        );
        return $this->dateElement->getHtml();
    }

    public function getSelectValues()
    {
        $exportModel = $this->exportFactory->create();
        $html = '';
        $statusValues = [];
        foreach ($exportModel->getEntities() as $entity => $label) {
            foreach ($this->allStatuses->toOptionArray(
                $entity
            ) as $status) {
                $statusValues[$entity][$status['value']] = $status['label'];
            }
        }
        $html .= $this->arrayToJsHash('status_values', $statusValues);

        $lastIncrementIds = [];
        foreach ($exportModel->getEntities() as $entity => $label) {
            $lastIncrementIds[$entity] = $this->entityHelper->getLastIncrementId($entity);
        }
        $html .= $this->arrayToJsHash('last_increment_ids', $lastIncrementIds);

        $lastExportedIds = [];
        $profileLinks = [];
        foreach ($this->profileSource->toOptionArray(
            false,
            false,
            true
        ) as $profile) {
            $lastExportedIds[$profile['value']] = $profile['last_exported_increment_id'];
            $profileLinks[$profile['value']] = $this->getUrl(
                'xtento_orderexport/profile/edit',
                ['id' => $profile['value']]
            );
        }
        $html .= $this->arrayToJsHash('last_exported_increment_ids', $lastExportedIds);
        $html .= $this->arrayToJsHash('profile_edit_links', $profileLinks);

        $profileSettings = [];
        $settingsToFetch = [
            'export_filter_datefrom',
            'export_filter_dateto',
            'export_filter_status',
            'export_filter_new_only',
            'export_action_change_status',
            'store_ids',
            'start_download_manual_export',
            'export_filter_last_x_days'
        ];
        foreach ($this->profileSource->toOptionArray(
            false,
            false,
            true
        ) as $profile) {
            foreach ($settingsToFetch as $setting) {
                $value = $profile['profile']->getData($setting);
                if (($setting == 'export_filter_datefrom' || $setting == 'export_filter_dateto') && !empty($value)) {
                    $value = $this->dateTimeFormatter->formatObject($this->_localeDate->date(new \DateTime($value)), $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT));
                }
                $profileSettings[$profile['value']][$setting] = $value;
            }
        }
        $html .= $this->arrayToJsHash('profile_settings', $profileSettings);

        return $html;
    }

    public function getStatuses($entity)
    {
        return $this->allStatuses->toOptionArray($entity);
    }

    public function getSession()
    {
        return $this->_session;
    }

    protected function arrayToJsHash($name, $array)
    {
        return 'window.' . $name . ' = $H(' . json_encode($array) . ");\n";
    }

    protected function _toHtml()
    {
        $messagesBlock = <<<EOT
<div id="messages">
    <div class="messages">
        <div class="message message-warning warning" id="warning-msg" style="display:none">
            <div id="warning-msg-text"></div>
        </div>
        <div class="message message-success success" id="success-msg" style="display:none">
            <div id="success-msg-text"></div>
        </div>
    </div>
</div>
EOT;
        return $messagesBlock . parent::_toHtml();
    }
}
