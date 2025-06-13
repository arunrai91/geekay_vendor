<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model\UrlParser\Url;

use Amasty\ShopbySeo\Model\UrlRewrite\IsExist as IsUrlRewriteExist;

/**
 * Parse relative url and return params from seo part, if possible.
 */
class ParseSeoUrl
{
    /**
     * @var IsAllowed
     */
    private $isAllowed;

    /**
     * @var SuffixManager
     */
    private $suffixManager;

    /**
     * @var RetrieveSeoPartAndIdentifier
     */
    private $retrieveSeoPartAndIdentifier;

    /**
     * @var IsUrlRewriteExist
     */
    private $isUrlRewriteExist;

    public function __construct(
        IsAllowed $isAllowed,
        SuffixManager $suffixManager,
        RetrieveSeoPartAndIdentifier $retrieveSeoPartAndIdentifier,
        IsUrlRewriteExist $isUrlRewriteExist
    ) {
        $this->isAllowed = $isAllowed;
        $this->suffixManager = $suffixManager;
        $this->retrieveSeoPartAndIdentifier = $retrieveSeoPartAndIdentifier;
        $this->isUrlRewriteExist = $isUrlRewriteExist;
    }

    /**
     * @param string $relativeUrl E.x. gear/bags/color-black.html
     * @return null|array [seoPart, identifier], null mean can't find seo part in given $relativeUrl
     */
    public function execute(string $relativeUrl): ?array
    {
        if (!$this->isAllowed->execute($relativeUrl)) {
            return null;
        }

        $identifierWithSeoPart = $this->suffixManager->removeSuffix($relativeUrl);
        $isSuffixRemoved = $relativeUrl !== $identifierWithSeoPart;

        if ($result = $this->retrieveSeoPartAndIdentifier->execute($identifierWithSeoPart)) {
            [$seoPart, $identifier] = $result;
        } else {
            return null;
        }

        if ($isSuffixRemoved) {
            $identifier = $this->suffixManager->addSuffix(ltrim($identifier, '/'));
        }

        if ($identifier && !$this->isUrlRewriteExist->execute($identifier)) {
            return null;
        }

        return [$seoPart, $identifier];
    }
}
