<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Api;

use Amasty\Feed\Api\Data\FeedTemplateInterface;

interface FeedTemplateRepositoryInterface
{
    /**
     * Retrieve template by value.
     *
     * @param string $value
     * @param string $field
     * @return FeedTemplateInterface
     */
    public function getBy(string $value, string $field): FeedTemplateInterface;

    /**
     * Save template.
     *
     * @param \Amasty\Feed\Api\Data\FeedTemplateInterface $templateModel
     * @return \Amasty\Feed\Api\Data\FeedTemplateInterface
     */
    public function save(FeedTemplateInterface $templateModel): FeedTemplateInterface;

    /**
     * Delete template.
     *
     * @param \Amasty\Feed\Api\Data\FeedTemplateInterface $templateModel
     * @return bool
     */
    public function delete(FeedTemplateInterface $templateModel): bool;

    /**
     * Delete template by id.
     *
     * @param int $templateId
     * @return bool
     */
    public function deleteById(int $templateId): bool;
}
