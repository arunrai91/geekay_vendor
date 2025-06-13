<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\GoogleWizard;

use Amasty\Feed\Model\Export\Product\Attributes\FeedAttributesStorage;

class Brand extends Element
{
    /**
     * @var string
     */
    protected $type = 'attribute';

    /**
     * @var string
     */
    protected $tag = 'g:brand';

    /**
     * @var string
     */
    protected $modify = 'html_escape';

    /**
     * @var string
     */
    protected $value = FeedAttributesStorage::PREFIX_PRODUCT_ATTRIBUTE . '|manufacturer';

    /**
     * @var string
     */
    protected $name = 'brand';

    /**
     * @var string
     */
    protected $description = 'Brand of the item';

    /**
     * @var int
     */
    protected $limit = 70;
}
