<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Block\Email;

use Amasty\Acart\Block\Email\Items\Quote;
use Magento\Framework\View\Element\Template;

class Items extends Template
{
    /**
     * @var array
     */
    protected $params = [
        'mode' => [
            'default' => 'table',
            'available' => [
                'list',
                'table'
            ]
        ],
        'showImage' => [
            'default' => 'yes',
            'available' => [
                'yes',
                'no'
            ]
        ],
        'showConfigurableImage' => [
            'default' => 'no',
            'available' => [
                'yes',
                'no'
            ]
        ],
        'showPrice' => [
            'default' => 'yes',
            'available' => [
                'yes',
                'no'
            ]
        ],
        'priceFormat' => [
            'default' => 'excludeTax',
            'available' => [
                'excludeTax',
                'includeTax'
            ]
        ],
        'showDescription' => [
            'default' => 'yes',
            'available' => [
                'yes',
                'no'
            ]
        ],
        'optionList' => [
            'default' => 'yes',
            'available' => [
                'yes',
                'no'
            ]
        ],
        'showSKU' => [
            'default' => 'no',
            'available' => [
                'yes',
                'no'
            ]
        ],
        'showQty' => [
            'default' => 'no',
            'available' => [
                'yes',
                'no'
            ]
        ],
    ];

    /**
     * @param string $key
     *
     * @return mixed
     */
    protected function _getLayoutParam($key)
    {
        $func = 'get' . $key;

        return in_array($this->$func(), $this->params[$key]['available']) ? $this->$func(
        ) : $this->params[$key]['default'];
    }

    /**
     * @param string $mode
     *
     * @return mixed
     */
    public function setMode($mode)
    {
        $this->setTemplate('email/' . $mode . '.phtml');

        return parent::setMode($mode);
    }

    /**
     * @return mixed
     */
    public function getMode()
    {
        return $this->_getLayoutParam('mode');
    }

    /**
     * @return bool
     */
    public function showImage()
    {
        return $this->_getLayoutParam('showImage') == 'yes';
    }

    /**
     * @return bool
     */
    public function showConfigurableImage()
    {
        return $this->_getLayoutParam('showConfigurableImage') == 'yes';
    }

    /**
     * @return bool
     */
    public function showPrice()
    {
        return $this->_getLayoutParam('showPrice') == 'yes';
    }

    public function showPriceIncTax(): bool
    {
        return $this->_getLayoutParam('priceFormat') == 'includeTax';
    }

    /**
     * @return bool
     */
    public function showDescription()
    {
        return $this->_getLayoutParam('showDescription') == 'yes';
    }

    /**
     * @return bool
     */
    public function showOptionList()
    {
        return $this->_getLayoutParam('optionList') == 'yes';
    }

    /**
     * @return bool
     */
    public function showSku()
    {
        return $this->_getLayoutParam('showSKU') == 'yes';
    }

    /**
     * @return bool
     */
    public function showQty()
    {
        return $this->_getLayoutParam('showQty') == 'yes';
    }

    public function getItems(): array
    {
        $items = [];
        if ($this->getQuoteId()) {
            /** @var Quote $childBlock */
            $childBlock = $this->getChildBlock('amasty.acart.items.data');
            $childBlock->setQuoteId($this->getQuoteId());
            $items = $childBlock->getItems();
        }

        return $items;
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Quote\Model\Quote $item
     * @return string
     */
    public function getItemBlockHtml($item): string
    {
        return $this->getChildBlock('amasty_product_card')
            ->setData('item', $item)
            ->setData('showConfigurableImage', $this->showConfigurableImage())
            ->setData('showImage', $this->showImage())
            ->setData('showPrice', $this->showPrice())
            ->setData('showPriceIncTax', $this->showPriceIncTax())
            ->setData('showDescription', $this->showDescription())
            ->setData('showOptionList', $this->showOptionList())
            ->setData('showSKU', $this->showSku())
            ->setData('showQty', $this->showQty())
            ->toHtml();
    }
}
