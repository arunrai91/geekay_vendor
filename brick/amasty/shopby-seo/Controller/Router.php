<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Controller;

use Amasty\ShopbyBase\Model\Redirect\NonSlash as NonSlashRedirectManager;
use Amasty\ShopbySeo\Helper\Data;
use Amasty\ShopbySeo\Helper\Url;
use Amasty\ShopbySeo\Helper\UrlParser;
use Amasty\ShopbySeo\Model\UrlRewrite\IsExist as IsUrlRewriteExist;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;

class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var bool
     */
    private $isSuffixRemoved = false;

    /**
     * @var Url
     */
    private $urlHelper;

    /**
     * @var UrlParser
     */
    private $urlParser;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var NonSlashRedirectManager
     */
    private $nonSlashRedirectManager;

    /**
     * @var IsUrlRewriteExist
     */
    private $isUrlRewriteExist;

    public function __construct(
        UrlParser $urlParser,
        Url $urlHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Data $helper,
        NonSlashRedirectManager $nonSlashRedirectManager,
        IsUrlRewriteExist $isUrlRewriteExist
    ) {
        $this->urlHelper = $urlHelper;
        $this->urlParser = $urlParser;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->nonSlashRedirectManager = $nonSlashRedirectManager;
        $this->isUrlRewriteExist = $isUrlRewriteExist;
    }

    /**
     * @param RequestInterface $request
     *
     * @return bool|ActionInterface
     */
    public function match(RequestInterface $request)
    {
        if ($request->getMetaData(Data::SKIP_REQUEST_FLAG) || $this->skipRequest($request)) {
            $request->setMetaData(Data::SKIP_REQUEST_FLAG, true);

            return false;
        }

        $this->initRequestMetaData($request);

        $pathInfo = $request->getPathInfo();
        $identifier = $this->removeSuffix($pathInfo, $request);
        $this->isSuffixRemoved = $pathInfo !== $identifier;

        list($seoPart, $identifier) = $this->getSeoPartAndIdentifier($identifier, $request);
        if ($request->getMetaData(Data::SKIP_REQUEST_FLAG) || $this->skipIdentifier($identifier)) {
            return false;
        }

        $params = $this->urlParser->parseSeoPart($seoPart);
        $this->checkSeoParams($request, $params);
        if (!empty($params)) {
            if ($this->nonSlashRedirectManager->isNeedRedirect($this->getSeoSuffix())) {
                return $this->nonSlashRedirectManager->createRedirect($this->getSeoSuffix());
            }
            $this->modifyRequest($request, $identifier, $params);
        }

        $request->setMetaData(Data::SKIP_REQUEST_FLAG, true);

        return false;
    }

    /**
     * @param $identifier
     * @param $request
     * @return array|bool
     */
    private function getSeoPartAndIdentifier($identifier, $request)
    {
        $seoPart = '';
        $filterWord = $this->helper->getFilterWord();
        if ($filterWord) {
            if (strpos($identifier, '/' . $filterWord . '/') !== false) {
                $filterWordPosition = strpos($identifier, '/' . $filterWord . '/');
                $seoPart = substr(
                    $identifier,
                    $filterWordPosition + strlen('/' . $filterWord . '/')
                );
                $identifier = substr($identifier, 0, $filterWordPosition);
            } else {
                $this->checkSeoParams($request);
                $request->setMetaData(Data::SKIP_REQUEST_FLAG, true);
            }
        } else {
            $lastSlashPosition = strrpos($identifier, "/");
            $lastSlashPosition = ($lastSlashPosition === false) ? 0 : $lastSlashPosition;
            $seoPart = substr($identifier, $lastSlashPosition + 1);
            $identifier = substr($identifier, 0, $lastSlashPosition);
        }

        return [$seoPart, $identifier];
    }

    /**
     * Used in \Amasty\ShopbyBrand\Plugin\ShopbySeo\Controller\Router\CheckForBrandUrl
     */
    public function skipIdentifier(string $identifier): bool
    {
        if ($identifier === '') {
            return false;
        }

        return !$this->isUrlRewriteExist->execute($this->addSuffix(ltrim($identifier, '/')));
    }

    /**
     * @param $identifier
     * @param $request
     *
     * @return string
     */
    private function removeSuffix($identifier, $request)
    {
        $seoSuffix = $this->getSeoSuffix();
        $identifier = strpos($seoSuffix, '/') !== false ? $identifier : rtrim($identifier, '/');

        if (trim($identifier, '/') && $seoSuffix) {
            $suffixPosition = strrpos($identifier, $seoSuffix);

            if ($suffixPosition !== false
                && ($suffixPosition == strlen($identifier) - strlen($seoSuffix))
            ) {
                $identifier = substr($identifier, 0, $suffixPosition);
                if (!$this->urlHelper->getAddSuffixSettingValue() && !$request->isAjax()) {
                    $request->setMetaData(Data::SEO_REDIRECT_MISSED_SUFFIX_FLAG, true);
                }
            } elseif ($this->urlHelper->getAddSuffixSettingValue() && !$request->isAjax()) {
                $request->setMetaData(Data::SEO_REDIRECT_MISSED_SUFFIX_FLAG, true);
            }
        }

        return $identifier;
    }

    private function addSuffix(string $identifier): string
    {
        if ($this->isSuffixRemoved) {
            $identifier .= $this->getSeoSuffix();
        }

        return $identifier;
    }

    /**
     * @param RequestInterface $request
     * @param $identifier
     * @param array $params
     *
     * @return $this
     */
    public function modifyRequest(RequestInterface $request, $identifier, $params = [])
    {
        if (strlen($identifier)) {
            $request->setMetaData(Data::HAS_ROUTE_PARAMS, true);
            $identifier = $this->addSuffix($identifier);
            $request->setPathInfo($identifier);
        }

        $request->setParams($params);
        $request->setMetaData(Data::HAS_PARSED_PARAMS, true);

        return $this;
    }

    /**
     * @param RequestInterface $request
     *
     * @return bool
     */
    public function skipRequest(RequestInterface $request)
    {
        if ($request->isXmlHttpRequest()
            && !$request->getParam('shopbyAjax')
            && !$request->getParam('is_scroll')
        ) {
            return true;
        }

        return !$this->helper->isAllowedRequest($request, true);
    }

    /**
     * @return string
     */
    public function getSeoSuffix()
    {
        return (string) $this->scopeConfig
            ->getValue('catalog/seo/category_url_suffix', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param RequestInterface $request
     * @param array $parsedParams
     *
     * @return $this
     */
    private function checkSeoParams(RequestInterface $request, array $parsedParams = [])
    {
        $userExtraParams = array_diff_assoc($request->getUserParams(), $parsedParams);
        if ($this->urlParser->checkSeoParams(array_merge((array)$request->getQuery(), $userExtraParams))
            && !$this->isAjax($request)
        ) {
            $request->setMetaData(Data::SEO_REDIRECT_FLAG, true);
        }

        return $this;
    }

    /**
     * @param RequestInterface $request
     *
     * @return bool
     */
    private function isAjax(RequestInterface $request)
    {
        return $request->isAjax();
    }

    /**
     * @param RequestInterface $request
     *
     * @return $this
     */
    public function initRequestMetaData(RequestInterface $request)
    {
        $request->setMetaData(Data::SEO_REDIRECT_FLAG, false);
        $request->setMetaData(Data::SEO_REDIRECT_MISSED_SUFFIX_FLAG, false);
        $request->setMetaData(Data::HAS_PARSED_PARAMS, false);

        return $this;
    }
}
