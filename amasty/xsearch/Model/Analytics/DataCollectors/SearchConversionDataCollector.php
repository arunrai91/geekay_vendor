<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\Analytics\DataCollectors;

use Amasty\Xsearch\Model\ResourceModel\UserSearch as UserSearchResource;
use Amasty\Xsearch\Model\UserSearch\GetLastUserSearch;
use Magento\Framework\Exception\NoSuchEntityException;

class SearchConversionDataCollector implements AnalyticsDataCollectorInterface
{
    public const IDENTIFIER = 'search_click';

    /**
     * @var GetLastUserSearch
     */
    private $getLastUserSearch;

    /**
     * @var UserSearchResource
     */
    private $userSearchResource;

    public function __construct(
        GetLastUserSearch $getLastUserSearch,
        UserSearchResource $userSearchResource
    ) {
        $this->getLastUserSearch = $getLastUserSearch;
        $this->userSearchResource = $userSearchResource;
    }

    /**
     * @return string[]
     */
    public function getIdentifiers(): array
    {
        return [self::IDENTIFIER];
    }

    public function collect(array $data): void
    {
        try {
            $userSearch = $this->getLastUserSearch->execute();
            $userSearch->setProductClick(1);
            $this->userSearchResource->save($userSearch);
        } catch (NoSuchEntityException $e) {
            return; //No action required
        }
    }
}
