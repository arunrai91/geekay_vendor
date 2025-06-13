<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\FeedTemplate;

use Amasty\Feed\Api\Data\FeedTemplateInterface;
use Amasty\Feed\Api\Data\FeedTemplateInterfaceFactory;
use Amasty\Feed\Api\FeedTemplateRepositoryInterface;
use Amasty\Feed\Model\FeedTemplate as Model;
use Amasty\Feed\Model\FeedTemplate\ResourceModel\FeedTemplate as ResourceModel;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class Repository implements FeedTemplateRepositoryInterface
{
    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var array
     */
    private $mapFields = [];

    /**
     * @var FeedTemplateInterfaceFactory
     */
    private $templateFactory;

    /**
     * @var ResourceModel
     */
    private $resourceModel;

    public function __construct(
        FeedTemplateInterfaceFactory $templateFactory,
        ResourceModel $resourceModel
    ) {
        $this->templateFactory = $templateFactory;
        $this->resourceModel = $resourceModel;
    }

    public function getBy(string $value, string $field = Model::TEMPLATE_ID): FeedTemplateInterface
    {
        if (($result = $this->getFromCache($field, $value)) !== null) {
            return $result;
        }

        $template = $this->templateFactory->create();
        $this->resourceModel->load($template, $value, $field);
        if (!$template->getTemplateId()) {
            throw new NoSuchEntityException(
                __('Template with specified %1 "%2" not found.', $field, $value)
            );
        }

        return $this->addToCache($field, $value, $template);
    }

    public function save(FeedTemplateInterface $templateModel): FeedTemplateInterface
    {
        try {
            if ($templateModel->getTemplateId()) {
                $templateModel = $this->getBy((string)$templateModel->getTemplateId())
                    ->addData($templateModel->getData());
            }
            $this->resourceModel->save($templateModel);
            $this->invalidateCache($templateModel);
        } catch (\Exception $e) {
            if ($templateModel->getTemplateId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save Template with ID %1. Error: %2',
                        [$templateModel->getTemplateId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new Template. Error: %1', $e->getMessage()));
        }

        return $templateModel;
    }

    public function delete(FeedTemplateInterface $templateModel): bool
    {
        try {
            $this->resourceModel->delete($templateModel);
            $this->invalidateCache($templateModel);
        } catch (\Exception $e) {
            if ($templateModel->getTemplateId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove Template with ID %1. Error: %2',
                        [$templateModel->getTemplateId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove Template. Error: %1', $e->getMessage()));
        }

        return true;
    }

    public function deleteById(int $templateId): bool
    {
        return $this->delete($this->getBy((string)$templateId));
    }

    private function getFromCache(string $field, string $value): ?FeedTemplateInterface
    {
        $key = $this->getKey($field, $value);

        return $this->cache[$key] ?? null;
    }

    private function addToCache(string $field, string $value, FeedTemplateInterface $template): FeedTemplateInterface
    {
        $key = $this->addIdToMap($template->getTemplateId(), $field, $value);

        return $this->cache[$key] = $template;
    }

    private function addIdToMap(int $id, string $field, string $value): string
    {
        return $this->mapFields[$id][] = $this->getKey($field, $value);
    }

    private function getKey(string $field, string $value): string
    {
        return sha1(sprintf('%s-%s', $field, $value));
    }

    private function invalidateCache(FeedTemplateInterface $template): void
    {
        $keys = $this->mapFields[$template->getTemplateId()] ?? [];
        foreach ($keys as $key) {
            unset($this->cache[$key]);
        }
    }
}
