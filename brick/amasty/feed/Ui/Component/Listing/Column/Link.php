<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Ui\Component\Listing\Column;

use Amasty\Feed\Model\Config\Source\FeedStatus;
use Magento\Framework\Escaper;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Link extends Column
{
    /**
     * @var UrlFactory
     */
    private $urlFactory;

    /**
     * @var Escaper
     */
    private $escaper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlFactory $urlFactory,
        Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlFactory = $urlFactory;
        $this->escaper = $escaper;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['entity_id'])) {
                    $storeId = $item['orig_store_id'] ?? $item['store_id'];
                    $filename = $item['filename'] . '.' . $item['feed_type']
                        . (empty($item['compress']) ? '' : '.' . $item['compress']);

                    if ($item['status'] == FeedStatus::READY && $item['products_amount'] != 0) {
                        $item['download_link'] =
                            $this->getDownloadHref($item['entity_id'], $storeId) .  "&file=" . $filename;
                    } else {
                        $item[$this->getData('name')] =  $this->escaper->escapeHtml($filename);
                    }
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param int $feedId
     * @param int $storeId
     *
     * @return string
     */
    private function getDownloadHref($feedId, $storeId)
    {
        $urlInstance = $this->urlFactory->create();

        $routeParams = [
            '_direct' => 'amfeed/feed/download',
            '_query' => [
                'id' => $feedId
            ]
        ];

        $href = $urlInstance
            ->setScope($storeId)
            ->getUrl(
                '',
                $routeParams
            );

        return $href;
    }
}
