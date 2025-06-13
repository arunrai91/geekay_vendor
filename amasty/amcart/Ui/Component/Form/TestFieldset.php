<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Ui\Component\Form;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Fieldset;

class TestFieldset extends Fieldset
{
    public const RULE_ID = 'id';

    public function __construct(
        ContextInterface $context,
        array $components = [],
        array $data = []
    ) {
        $componentVisible = $context->getRequestParam(self::RULE_ID) ? true : false;

        if (isset($data['config']) && !$componentVisible) {
            $data['config']['componentDisabled'] = true;
        }

        parent::__construct($context, $components, $data);
    }
}
