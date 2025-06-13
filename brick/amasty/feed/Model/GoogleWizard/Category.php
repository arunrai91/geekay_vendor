<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\GoogleWizard;

use Amasty\Feed\Model\Export\Product\Attributes\FeedAttributesStorage;
use Amasty\Feed\Model\RegistryContainer;

class Category extends Element
{
    /**
     * @var string
     */
    protected $type = RegistryContainer::TYPE_CATEGORY;

    /**
     * @var string
     */
    protected $tag = 'g:google_product_category';

    /**
     * @var string
     */
    protected $modify = 'html_escape|length:150';

    public function setValue($value)
    {
        $this->value = FeedAttributesStorage::PREFIX_MAPPED_CATEGORY_ATTRIBUTE . '|' . $value;
    }
}
