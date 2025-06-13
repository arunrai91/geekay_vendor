<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Api\Data;

interface VisitHistoryDetailInterface
{
    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int|null $id
     *
     * @return \Amasty\AdminActionsLog\Api\Data\VisitHistoryDetailInterface
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getVisitId(): ?int;

    /**
     * @param int $visitId
     *
     * @return \Amasty\AdminActionsLog\Api\Data\VisitHistoryDetailInterface
     */
    public function setVisitId(int $visitId): VisitHistoryDetailInterface;

    /**
     * @return string|null
     */
    public function getPageName(): ?string;

    /**
     * @param string $pageName
     *
     * @return \Amasty\AdminActionsLog\Api\Data\VisitHistoryDetailInterface
     */
    public function setPageName(string $pageName): VisitHistoryDetailInterface;

    /**
     * @return string|null
     */
    public function getPageUrl(): ?string;

    /**
     * @param string $pageUrl
     *
     * @return \Amasty\AdminActionsLog\Api\Data\VisitHistoryDetailInterface
     */
    public function setPageUrl(string $pageUrl): VisitHistoryDetailInterface;

    /**
     * @return int|null
     */
    public function getStayDuration(): ?int;

    /**
     * @param int $stayDuration
     *
     * @return \Amasty\AdminActionsLog\Api\Data\VisitHistoryDetailInterface
     */
    public function setStayDuration(int $stayDuration): VisitHistoryDetailInterface;

    /**
     * @return string|null
     */
    public function getSessionId(): ?string;

    /**
     * @param string $sessionId
     *
     * @return \Amasty\AdminActionsLog\Api\Data\VisitHistoryDetailInterface
     */
    public function setSessionId(string $sessionId): VisitHistoryDetailInterface;
}
