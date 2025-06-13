<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\Redirect;

use Magento\Framework\App\Action\Redirect;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\UrlInterface;

class NonSlash
{
    private const HTTP_REDIRECT_CODE = 301;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ActionFactory
     */
    private $actionFactory;

    /**
     * @var ResponseInterface
     */
    private $response;

    public function __construct(
        RequestInterface $request,
        UrlInterface $urlBuilder,
        ActionFactory $actionFactory,
        ResponseInterface $response
    ) {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->actionFactory = $actionFactory;
        $this->response = $response;
    }

    public function createRedirect(?string $allowedSuffix): Redirect
    {
        $this->response->setRedirect($this->urlBuilder->getUrl('', [
            '_direct' => $this->cutExtraSlash($this->request->getPathInfo(), $allowedSuffix),
            '_query' => $this->request->getParams()
        ]), self::HTTP_REDIRECT_CODE);
        $this->request->setDispatched(true);

        return $this->actionFactory->create(Redirect::class);
    }

    public function isNeedRedirect(?string $allowedSuffix): bool
    {
        $requestPath = $this->request->getPathInfo();

        if ($this->endsWith($requestPath, $allowedSuffix)) {
            return false;
        }

        if (rtrim($requestPath, '/') !== $requestPath) {
             return true;
        }

        return false;
    }

    private function endsWith(string $requestPath, ?string $suffix): bool
    {
        if (!$suffix) {
            return false;
        }

        $trimmedSuffix = rtrim($suffix, '/');
        if ($slashInSuffixCount = strlen($suffix) - strlen($trimmedSuffix)) {
            $requestPath = substr($requestPath, 0, -$slashInSuffixCount);
            if (rtrim($requestPath, '/') !== $requestPath) {
                return false;
            }
            if (!$trimmedSuffix) {
                return true;
            }
            $suffix = $trimmedSuffix;
        }

        return substr($requestPath, -strlen($suffix)) === $suffix;
    }

    private function cutExtraSlash(string $requestPath, ?string $suffix): string
    {
        $trimmedRequestPath = trim($requestPath, '/');
        if (!$suffix) {
            return $trimmedRequestPath;
        }

        $trimmedSuffix = trim($suffix, '/');
        if ($trimmedSuffix) {
            $trimmedRequestPath = substr($trimmedRequestPath, 0, -strlen($trimmedSuffix));
        }

        return $trimmedRequestPath . $suffix;
    }
}
