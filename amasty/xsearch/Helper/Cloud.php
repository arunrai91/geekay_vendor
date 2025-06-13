<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Helper;

use Amasty\Xsearch\Model\ResourceModel\Category\Fulltext\CollectionFactory;
use Amasty\Xsearch\Model\ResourceModel\Page\Fulltext\CollectionFactory as PageCollectionFactory;

class Cloud
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var PageCollectionFactory
     */
    private $factory;

    public function __construct(
        CollectionFactory $collectionFactory,
        PageCollectionFactory $factory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->factory = $factory;
    }
}
