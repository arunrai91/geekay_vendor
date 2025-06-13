<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model;

use Amasty\Feed\Api\Data\FeedTemplateInterface;
use Amasty\Feed\Model\FeedTemplate\ResourceModel\FeedTemplate as ResourceModel;
use Magento\Framework\Model\AbstractModel;

class FeedTemplate extends AbstractModel implements FeedTemplateInterface
{
    public const TEMPLATE_ID = 'id';
    public const TEMPLATE_CODE = 'template_code';
    public const TEMPLATE_NAME = 'template_name';
    public const TEMPLATE_TYPE = 'template_type';
    public const TEMPLATE_CONFIG = 'template_config';
    public const TEMPLATE_CONTENT = 'template_content';
    public const DYNAMIC_FIELDS = 'dynamic_fields';

    protected function _construct()
    {
        $this->_init(ResourceModel::class);
        $this->setIdFieldName(self::TEMPLATE_ID);
    }

    public function getTemplateId(): int
    {
        return (int)$this->getData(self::TEMPLATE_ID);
    }

    public function setTemplateId(int $templateId): void
    {
        $this->setData(self::TEMPLATE_ID, $templateId);
    }

    public function getTemplateCode(): string
    {
        return (string)$this->getData(self::TEMPLATE_CODE);
    }

    public function setTemplateCode(string $templateCode): void
    {
        $this->setData(self::TEMPLATE_CODE, $templateCode);
    }

    public function getTemplateName(): string
    {
        return (string)$this->getData(self::TEMPLATE_NAME);
    }

    public function setTemplateName(string $templateName): void
    {
        $this->setData(self::TEMPLATE_NAME, $templateName);
    }

    public function getTemplateType(): string
    {
        return (string)$this->getData(self::TEMPLATE_TYPE);
    }

    public function setTemplateType(string $templateType): void
    {
        $this->setData(self::TEMPLATE_TYPE, $templateType);
    }

    public function getTemplateConfig(): string
    {
        return (string)$this->getData(self::TEMPLATE_CONFIG);
    }

    public function setTemplateConfig(string $templateConfig): void
    {
        $this->setData(self::TEMPLATE_CONFIG, $templateConfig);
    }

    public function getTemplateContent(): string
    {
        return (string)$this->getData(self::TEMPLATE_CONTENT);
    }

    public function setTemplateContent(string $templateContent): void
    {
        $this->setData(self::TEMPLATE_CONTENT, $templateContent);
    }

    public function getDynamicFields(): ?string
    {
        return $this->hasData(self::DYNAMIC_FIELDS) ? (string)$this->getData(self::DYNAMIC_FIELDS) : null;
    }

    public function setDynamicFields(string $dynamicFields): void
    {
        $this->setData(self::DYNAMIC_FIELDS, $dynamicFields);
    }
}
