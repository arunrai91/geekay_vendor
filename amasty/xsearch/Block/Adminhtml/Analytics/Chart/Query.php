<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Block\Adminhtml\Analytics\Chart;

use Amasty\Xsearch\Model\QueryInfo;
use Amasty\Xsearch\Model\Report\DataFilterProvider;
use Amasty\Xsearch\Model\ResourceModel\UserSearch;
use Magento\Backend\Block\Template;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Phrase;
use Amasty\Xsearch\Model\ResourceModel\UserSearch\CollectionFactory;
use Amasty\Xsearch\Model\ResourceModel\UserSearch\Collection;

class Query extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_Xsearch::analytics/chart/query.phtml';

    /**
     * @var EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var null|array
     */
    private $totals;

    /**
     * @var QueryInfo
     */
    private $queryInfo;

    /**
     * @var UserSearch
     */
    private $searchResource;

    /**
     * @var DataFilterProvider
     */
    private $dataFilterProvider;

    public function __construct(
        EncoderInterface $jsonEncoder,
        Template\Context $context,
        ?CollectionFactory $userSearchCollection, // TODO remove
        QueryInfo $queryInfo,
        array $data = [],
        UserSearch $searchResource = null, // TODO move to not optional
        DataFilterProvider $dataFilterProvider = null
    ) {
        parent::__construct($context, $data);
        $this->jsonEncoder = $jsonEncoder;
        $this->queryInfo = $queryInfo;
        // OM for backward compatibility
        $this->searchResource = $searchResource ?? ObjectManager::getInstance()->get(UserSearch::class);
        $this->dataFilterProvider = $dataFilterProvider ?? ObjectManager::getInstance()->get(DataFilterProvider::class);
    }

    /**
     * @return Phrase
     */
    public function getTitle()
    {
        return __('Search Volume');
    }

    /**
     * @return string
     */
    public function getAnalyticsData()
    {
        return $this->jsonEncoder->encode(
            $this->queryInfo->getAnalyticsData(Collection::GROUP_BY_MONTH_PATTERN)
        );
    }

    /**
     * @param string $field
     * @return int
     */
    public function getTotal($field)
    {
        if (!$this->totals) {
            $fromDate = $this->dataFilterProvider->getFromDate();
            $toDate = $this->dataFilterProvider->getToDate();
            $analyticsData = current($this->searchResource->requestAnalyticsData($fromDate, $toDate));

            $userTotal = $analyticsData[QueryInfo::KEY_UNIQUE_USER];
            if ($userTotal) {
                $productClickUsers = $this->searchResource->getProductClickUsersTotal($fromDate, $toDate);
                $analyticsData[QueryInfo::KEY_PRODUCT_CLICK] = round(($productClickUsers / $userTotal) * 100, 2);
            } else {
                $analyticsData[QueryInfo::KEY_PRODUCT_CLICK] = 0;
            }

            $this->totals = $analyticsData;
        }

        return $this->totals[$field] ?? 0;
    }
}
