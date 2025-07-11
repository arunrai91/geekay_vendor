<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Model\Config\Backend;

use Amasty\Base\Model\Source\NotificationType;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\ResourceModel\AbstractResource;

class Unsubscribe extends \Magento\Framework\App\Config\Value implements
    \Magento\Framework\App\Config\Data\ProcessorInterface
{
    public const PATH_TO_FEED_IMAGES = 'https://feed.amasty.net/news/unsubscribe/';

    /**
     * @var \Amasty\Base\Model\AdminNotification\Messages
     */
    private $messageManager;

    /**
     * @var NotificationType
     */
    private $notificationType;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Amasty\Base\Model\AdminNotification\Messages $messageManager,
        NotificationType $notificationType,
        AbstractResource $resource,
        AbstractDb $resourceCollection,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->messageManager = $messageManager;
        $this->notificationType = $notificationType;
    }

    public function afterSave()
    {
        if ($this->isValueChanged()) {
            $this->prepareMessage();
        }

        return parent::afterSave();
    }

    private function prepareMessage()
    {
        $value = explode(',', $this->getValue());
        if (in_array(NotificationType::UNSUBSCRIBE_ALL, $value, true)) {
            $changes = [NotificationType::UNSUBSCRIBE_ALL];
        } else {
            $oldValue = explode(',', $this->getOldValue());
            $changes = array_diff($oldValue, $value);
            $changes = array_diff($changes, [NotificationType::UNSUBSCRIBE_ALL]);
        }

        if (!empty(array_filter($changes))) {
            foreach ($changes as $change) {
                $message = $this->generateMessage($change);
                $this->messageManager->addMessage($message);
            }
        } else {
            $this->messageManager->clear();
        }
    }

    /**
     * Process config value
     *
     * @param string $value
     * @return string
     */
    public function processValue($value)
    {
        return $value;
    }

    private function generateMessage($change)
    {
        $message = '';
        $titles = $this->notificationType->toOptionArray();
        foreach ($titles as $title) {
            if ($title['value'] === $change) {
                if ($change === NotificationType::UNSUBSCRIBE_ALL) {
                    $label = __('All Notifications');
                } else {
                    $label = $title['label'];
                }

                $message = '<img src="' . $this->generateLink($change) .'"/><span>'
                    . __('You have successfully unsubscribed from %1.', $label) .'</span>';
                break;
            }
        }

        return $message;
    }

    private function generateLink($change)
    {
        $change = mb_strtolower($change);
        return self::PATH_TO_FEED_IMAGES . $change . '.svg';
    }
}
