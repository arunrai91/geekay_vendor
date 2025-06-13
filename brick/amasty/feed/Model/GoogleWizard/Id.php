<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\GoogleWizard;

use Amasty\Feed\Model\Export\Product\Attributes\FeedAttributesStorage;

class Id extends Element
{
    /**
     * @var string
     */
    protected $type = 'attribute';

    /**
     * @var string
     */
    protected $tag = 'g:id';

    /**
     * @var int
     */
    protected $limit = 50;

    /**
     * @var string
     */
    protected $modify = 'html_escape';

    /**
     * @var string
     */
    protected $value = FeedAttributesStorage::PREFIX_BASIC_ATTRIBUTE . '|sku';

    /**
     * @var bool
     */
    protected $required = true;

    /**
     * @var string
     */
    protected $name = 'id';

    /**
     * @var string
     */
    protected $description = 'An identifier of the item';
}
