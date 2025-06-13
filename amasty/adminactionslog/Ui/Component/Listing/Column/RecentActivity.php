<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Ui\Component\Listing\Column;

use Amasty\AdminActionsLog\Model\ActiveSession\ActiveSession;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class RecentActivity extends Column
{
    public const MINUTE = 60;
    public const HOUR = 3600;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        DateTime $dateTime,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->dateTime = $dateTime;
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item[ActiveSession::RECENT_ACTIVITY])) {
                    $item[ActiveSession::RECENT_ACTIVITY] = $this->decorateRecentActivity(
                        $item[ActiveSession::RECENT_ACTIVITY]
                    );
                }
            }
        }

        return $dataSource;
    }

    public function decorateRecentActivity(string $rowTime): string
    {
        $timeDifference = $this->dateTime->timestamp() - strtotime($rowTime);

        if ($timeDifference < self::MINUTE) {
            return __('Just Now')->render();
        } elseif ($timeDifference < self::HOUR) {
            $minutes = round($timeDifference / self::MINUTE);
            return  __($minutes . " minute(s) ago")->render();
        } else {
            $hours = round($timeDifference / self::HOUR);
            return __($hours . " hour(s) ago")->render();
        }
    }
}
