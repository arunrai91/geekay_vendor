<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\System\Config\Comment;

use Magento\Config\Model\Config\CommentInterface;
use Magento\Framework\Phrase;
use Magento\Framework\UrlInterface;

class SearchAttribute implements CommentInterface
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
     * @param string $elementValue
     * @return Phrase
     * @suppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getCommentText($elementValue): Phrase
    {
        return __(
            'The configuration settings are limited up to 500 attributes.'
            . ' If you have more than 500 attributes created, please manage them through the'
            . ' <a href="%1" target="_blank">Product Attributes grid.</a>',
            $this->urlBuilder->getUrl('catalog/product_attribute/index')
        );
    }
}
