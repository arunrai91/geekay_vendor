<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Block\Adminhtml\Form\Field;

use Amasty\Xsearch\Helper\Data;
use Amasty\Xsearch\Model\CustomSearchAttributes;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class Attributes extends Select
{
    public const EXCLUDED_ATTRIBUTES = ['category_ids', 'visibility'];

    /**
     * @var Data
     */
    private $xSearchHelper;

    /**
     * @var CustomSearchAttributes
     */
    private $customSearchAttributes;

    public function __construct(
        Data $xSearchHelper,
        Context $context,
        array $data = [],
        CustomSearchAttributes $customSearchAttributes = null
    ) {
        parent::__construct($context, $data);
        $this->xSearchHelper = $xSearchHelper;
        $this->customSearchAttributes =
            $customSearchAttributes ?? ObjectManager::getInstance()->get(CustomSearchAttributes::class);
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        $productAttributes = $this->xSearchHelper->getProductAttributes();
        foreach ($productAttributes as $attribute) {
            if (!in_array($attribute->getAttributeCode(), self::EXCLUDED_ATTRIBUTES)) {
                $this->addOption(
                    $attribute->getAttributeCode(),
                    $this->escapeQuote((string)$attribute->getFrontendLabel())
                );
            }
        }

        foreach ($this->customSearchAttributes->getAttributes() as $code => $attributeConfig) {
            $this->addOption(
                $code,
                (string)$attributeConfig['label']
            );
        }

        return parent::_toHtml();
    }

    /**
     * @param $value
     * @return mixed
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
