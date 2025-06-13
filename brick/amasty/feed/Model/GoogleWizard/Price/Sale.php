<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\GoogleWizard\Price;

use Amasty\Feed\Model\Export\Product\Attributes\FeedAttributesStorage;
use Amasty\Feed\Model\GoogleWizard\Element;

class Sale extends Element
{
    /**
     * @var string
     */
    protected $type = 'attribute';

    /**
     * @var string
     */
    protected $tag = 'g:sale_price';

    /**
     * @var string
     */
    protected $format = 'price';

    /**
     * @var string
     */
    protected $value = FeedAttributesStorage::PREFIX_PRODUCT_ATTRIBUTE . '|special_price';

    /**
     * @var string
     */
    protected $name = 'sale price';

    /**
     * @var string
     */
    protected $description = 'Advertised sale price of the item';
}
