<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Plugin;

use Amasty\ShopbySeo\Helper\Meta;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\Controller\ResultInterface;

class CategoryViewPlugin
{
    /**
     * @var Meta
     */
    private $metaHelper;

    public function __construct(Meta $metaHelper)
    {
        $this->metaHelper = $metaHelper;
    }

    /**
     * @param ActionInterface $subject
     * @param Page $result
     * @return ResultInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(ActionInterface $subject, $result)
    {
        if ($result instanceof Page) {
            $this->metaHelper->setPageTags($result->getConfig());
        }

        return $result;
    }
}
