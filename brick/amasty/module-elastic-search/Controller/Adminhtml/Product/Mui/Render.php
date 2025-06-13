<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Controller\Adminhtml\Product\Mui;

use Amasty\ElasticSearch\Controller\Adminhtml\RelevanceRule\AbstractRelevance;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Ui\Controller\Adminhtml\Index\Render as MagentoUiRenderController;

class Render extends MagentoUiRenderController implements HttpGetActionInterface, HttpPostActionInterface
{
    public const ADMIN_RESOURCE = AbstractRelevance::ADMIN_RESOURCE;
}
