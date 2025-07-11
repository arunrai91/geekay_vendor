<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Analytics;

use Amasty\Mostviewed\Api\Data\ClickInterface;
use Magento\Framework\Model\AbstractModel;
use Amasty\Mostviewed\Model\ResourceModel\Analytics\View as ViewResource;
use Amasty\Mostviewed\Api\Data\ViewInterface;

class View extends AbstractModel implements ViewInterface
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(ViewResource::class);
    }

    /**
     * @return string
     */
    public function getVisitorId()
    {
        return $this->getData(ViewInterface::VISITOR_ID);
    }

    /**
     * @param string $visitorId
     *
     * @return \Amasty\Mostviewed\Api\Data\ViewInterface
     */
    public function setVisitorId($visitorId)
    {
        return $this->setData(ViewInterface::VISITOR_ID, $visitorId);
    }

    /**
     * @return int
     */
    public function getBlockId()
    {
        return $this->getData(ViewInterface::BLOCK_ID);
    }

    /**
     * @param int $blockId
     *
     * @return \Amasty\Mostviewed\Api\Data\ViewInterface
     */
    public function setBlockId($blockId)
    {
        return $this->setData(ViewInterface::BLOCK_ID, $blockId);
    }

    public function getDate(): ?string
    {
        return $this->hasData(ViewInterface::DATE)
            ? (string)$this->_getData(ViewInterface::DATE)
            : null;
    }

    public function setDate(string $date): void
    {
        $this->setData(ViewInterface::DATE, $date);
    }
}
