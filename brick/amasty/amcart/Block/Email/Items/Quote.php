<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Block\Email\Items;

use Amasty\Acart\Model\ConfigProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Quote\Api\CartRepositoryInterface;

class Quote extends Template
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Template\Context $context,
        CartRepositoryInterface $cartRepository,
        array $data = [],
        ?ConfigProvider $configProvider = null // TODO move to not optional
    ) {
        parent::__construct($context, $data);
        $this->cartRepository = $cartRepository;
        // OM for backward compatibility
        $this->configProvider = $configProvider ?? ObjectManager::getInstance()->get(ConfigProvider::class);
    }

    public function getItems(): array
    {
        $items = $this->getQuote() ? $this->getQuote()->getAllVisibleItems() : [];
        $abandonedProductLimit = $this->configProvider->getAbandonedProductLimit();
        if ($abandonedProductLimit && !empty($items) && count($items) > $abandonedProductLimit) {
            $items = array_slice($items, 0, $abandonedProductLimit);
        }

        return $items;
    }

    public function getQuote()
    {
        try {
            return $this->cartRepository->get((int)$this->getQuoteId());
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
}
