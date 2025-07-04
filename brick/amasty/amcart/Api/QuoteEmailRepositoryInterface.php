<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Api;

use Amasty\Acart\Api\Data\QuoteEmailInterface;

interface QuoteEmailRepositoryInterface
{
    /**
     * @param int $id
     * @return \Amasty\Acart\Api\Data\QuoteEmailInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function getById(int $id): QuoteEmailInterface;

    /**
     * @param int $quoteId
     * @return \Amasty\Acart\Api\Data\QuoteEmailInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function getByQuoteId(int $quoteId): QuoteEmailInterface;

    /**
     * @param \Amasty\Acart\Api\Data\QuoteEmailInterface $quoteEmail
     *
     * @return \Amasty\Acart\Api\Data\QuoteEmailInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(QuoteEmailInterface $quoteEmail): QuoteEmailInterface;

    /**
     * @param \Amasty\Acart\Api\Data\QuoteEmailInterface $quoteEmail
     *
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(QuoteEmailInterface $quoteEmail): bool;

    /**
     * @param int $id
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NotFoundException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById(int $id): bool;
}
