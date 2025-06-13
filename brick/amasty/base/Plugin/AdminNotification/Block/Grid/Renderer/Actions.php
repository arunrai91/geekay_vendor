<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Plugin\AdminNotification\Block\Grid\Renderer;

use Magento\AdminNotification\Block\Grid\Renderer\Actions as NativeActions;
use Magento\Framework\DataObject;
use Magento\Framework\UrlInterface;

class Actions
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRender(NativeActions $subject, string $result, DataObject $row): string
    {
        if ($row->getData('is_amasty')) {
            $result .= sprintf(
                '<a class="action" href="%s" title="%2$s">%2$s</a>',
                $this->urlBuilder->getUrl('ambase/notification/frequency', ['action' => 'less']),
                __('Show less of these messages')
            );
            $result .= sprintf(
                '<a class="action" href="%s" title="%2$s">%2$s</a>',
                $this->urlBuilder->getUrl('ambase/notification/frequency', ['action' => 'more']),
                __('Show more of these messages')
            );
            $result .= sprintf(
                '<a class="action" href="%s" title="%2$s">%2$s</a>',
                $this->urlBuilder->getUrl(
                    'adminhtml/system_config/edit',
                    ['section' => 'amasty_base', '_fragment' => 'amasty_base_notifications-head']
                ),
                __('Unsubscribe')
            );
        }

        return $result;
    }
}
