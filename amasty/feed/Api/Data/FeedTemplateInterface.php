<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Api\Data;

interface FeedTemplateInterface
{
    /**
     * @return int
     */
    public function getTemplateId(): int;

    /**
     * @param int $templateId
     * @return void
     */
    public function setTemplateId(int $templateId): void;

    /**
     * @return string
     */
    public function getTemplateCode(): string;

    /**
     * @param string $templateCode
     * @return void
     */
    public function setTemplateCode(string $templateCode): void;

    /**
     * @return string
     */
    public function getTemplateName(): string;

    /**
     * @param string $templateName
     * @return void
     */
    public function setTemplateName(string $templateName): void;

    /**
     * @return string
     */
    public function getTemplateType(): string;

    /**
     * @param string $templateType
     * @return void
     */
    public function setTemplateType(string $templateType): void;

    /**
     * @return string
     */
    public function getTemplateConfig(): string;

    /**
     * @param string $templateConfig
     * @return void
     */
    public function setTemplateConfig(string $templateConfig): void;

    /**
     * @return string
     */
    public function getTemplateContent(): string;

    /**
     * @param string $templateContent
     * @return void
     */
    public function setTemplateContent(string $templateContent): void;

    /**
     * @return string|null
     */
    public function getDynamicFields(): ?string;

    /**
     * @param string $dynamicFields
     * @return void
     */
    public function setDynamicFields(string $dynamicFields): void;
}
