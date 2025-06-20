<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Controller\Adminhtml\Geoip;

use Amasty\Geoip\Controller\Adminhtml\GeoipAbstract as GeoipAbstract;

class Process extends GeoipAbstract
{
    public function execute()
    {
        $result = [];
        try {
            $type = $this->getRequest()->getParam('type');
            $action = $this->getRequest()->getParam('action');
            $filePath = $this->geoipHelper->getCsvFilePath($type);
            $ret = $this->importModel->doProcess($type, $filePath, $action);
            $result['type'] = $type;
            $result['status'] = 'processing';
            $result['position'] = ceil($ret['current_row'] / $ret['rows_count'] * 100);

            if ($result['position'] > 100) {
                $result['position'] = 100;
            }
            if ($type == 'block' && $result['position'] == 100 && $ret['current_row'] + 3 < $ret['rows_count']) {
                $result['position'] = 99;
            }
        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
        }

        $this->getResponse()->setBody($this->jsonHelper->jsonEncode($result));

        return $this->getResponse();
    }
}
