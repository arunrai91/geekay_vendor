<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\VisitHistoryEntry;

use Amasty\AdminActionsLog\Api\Data\VisitHistoryDetailInterface;
use Magento\Framework\Model\AbstractModel;

class VisitHistoryDetail extends AbstractModel implements VisitHistoryDetailInterface
{
    public const ID = 'id';
    public const VISIT_ID = 'visit_id';
    public const PAGE_NAME = 'page_name';
    public const PAGE_URL = 'page_url';
    public const STAY_DURATION = 'stay_duration';
    public const SESSION_ID = 'session_id';

    public function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\VisitHistoryDetail::class);
        $this->setIdFieldName(self::ID);
    }

    public function getVisitId(): ?int
    {
        return $this->hasData(self::VISIT_ID) ? (int)$this->_getData(self::VISIT_ID) : null;
    }

    public function setVisitId(int $visitId): VisitHistoryDetailInterface
    {
        $this->setData(self::VISIT_ID, $visitId);

        return $this;
    }

    public function getPageName(): ?string
    {
        return $this->_getData(self::PAGE_NAME);
    }

    public function setPageName(string $pageName): VisitHistoryDetailInterface
    {
        $this->setData(self::PAGE_NAME, $pageName);

        return $this;
    }

    public function getPageUrl(): ?string
    {
        return $this->_getData(self::PAGE_URL);
    }

    public function setPageUrl(string $pageUrl): VisitHistoryDetailInterface
    {
        $this->setData(self::PAGE_URL, $pageUrl);

        return $this;
    }

    public function getStayDuration(): ?int
    {
        return $this->hasData(self::STAY_DURATION) ? (int)$this->_getData(self::STAY_DURATION) : null;
    }

    public function setStayDuration(int $stayDuration): VisitHistoryDetailInterface
    {
        $this->setData(self::STAY_DURATION, $stayDuration);

        return $this;
    }

    public function getSessionId(): ?string
    {
        return $this->_getData(self::SESSION_ID);
    }

    public function setSessionId(string $sessionId): VisitHistoryDetailInterface
    {
        $this->setData(self::SESSION_ID, $sessionId);

        return $this;
    }
}
