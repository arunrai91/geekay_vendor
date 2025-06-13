<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Block\Adminhtml\Feed;

use Amasty\Feed\Model\ResourceModel\Feed\CollectionFactory;
use Magento\Backend\Block\Widget\Context;

class Template extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var CollectionFactory
     */
    private $feedCollectionFactory;

    public function __construct(
        Context $context,
        CollectionFactory $feedCollectionFactory,
        array $data = []
    ) {
        $this->feedCollectionFactory = $feedCollectionFactory;
        parent::__construct($context, $data);

        $this->addSetupGoogleFeedButton();
        $this->addWizardButton();
    }

    /**
     * Add setup google wizard button
     *
     * @return $this
     */
    public function addSetupGoogleFeedButton()
    {
        $this->addButton(
            'googleFeed',
            [
                'label'   => __("Setup Google Feed"),
                'class'   => 'google-feed primary',
                'onclick' => 'setLocation(\'' . $this->getCreateGoogleFeedUrl()
                    . '\')'
            ]
        );

        return $this;
    }

    private function addWizardButton()
    {
        $this->addButton(
            'wizard',
            [
                'label' => __('Add New Feed'),
                'class' => 'feed-wizard primary',
                'onclick' => 'setLocation(\'' . $this->getWizardUrl()
                    . '\')'
            ]
        );
    }

    /**
     * Get new url
     *
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new');
    }

    /**
     * Get google feed url
     *
     * @return string
     */
    public function getCreateGoogleFeedUrl()
    {
        return $this->getUrl('*/googleWizard/index');
    }

    public function getWizardUrl(): string
    {
        return $this->getUrl('*/wizard/index');
    }
}
