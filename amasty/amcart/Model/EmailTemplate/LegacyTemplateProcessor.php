<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Model\EmailTemplate;

use Amasty\Acart\Model\EmailTemplate\ProcessorPool\LegacyTemplateProcessorInterface;
use Magento\Framework\Mail\TemplateInterface;

class LegacyTemplateProcessor
{
    /**
     * @var array
     */
    private $templateProcessors = [];

    /**
     * @var LegacyTemplateProcessorInterface
     */
    private $defaultProcessor;

    public function __construct(
        LegacyTemplateProcessorInterface $defaultProcessor,
        $templateProcessors = []
    ) {
        foreach ($templateProcessors as $processor) {
            if (!($processor instanceof LegacyTemplateProcessorInterface)) {
                throw new \LogicException(
                    sprintf(
                        'Legacy Template Processor %s must implement %s',
                        get_class($processor),
                        LegacyTemplateProcessorInterface::class
                    )
                );
            }
        }

        $this->templateProcessors = $templateProcessors;
        $this->defaultProcessor = $defaultProcessor;
    }

    public function execute(TemplateInterface $template)
    {
        /** @var $processor LegacyTemplateProcessorInterface */
        foreach ($this->templateProcessors as $targetClass => $processor) {
            if (is_a($template, $targetClass)) {
                return $processor->execute($template);
            }
        }

        return $this->defaultProcessor->execute($template);
    }
}
