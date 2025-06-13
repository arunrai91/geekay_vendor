<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Block\Widget;

use Amasty\Xsearch\ViewModel\FormMiniData;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class Search extends Template implements BlockInterface
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_Xsearch::components/search.phtml';

    /**
     * @var FormMiniData
     */
    private $viewModel;

    public function __construct(
        FormMiniData $viewModel,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->viewModel = $viewModel;
    }

    /**
     * @return FormMiniData
     */
    public function getViewModel(): FormMiniData
    {
        return $this->viewModel;
    }
}
