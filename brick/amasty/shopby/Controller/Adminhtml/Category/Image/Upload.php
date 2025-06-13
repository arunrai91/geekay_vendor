<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Controller\Adminhtml\Category\Image;

use Magento\Backend\App\Action;
use Magento\Backend\Model\Session;
use Magento\Catalog\Controller\Adminhtml\Category;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context as ActionContext;
use Magento\Catalog\Model\ImageUploader;

class Upload extends Action
{
    /**
     * @var ImageUploader
     */
    private ImageUploader $imageUploader;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var Session
     */
    private Session $session;

    public function __construct(
        ActionContext $context,
        ImageUploader $imageUploader
    ) {
        parent::__construct($context);
        $this->imageUploader = $imageUploader;
        $this->request = $context->getRequest();
        $this->session = $context->getSession();
    }

    /**
     * Upload file controller action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $files = $this->request->getFiles();
            $result = $this->imageUploader->saveFileToTmpDir(key((array)$files));

            $result['cookie'] = [
                'name' => $this->session->getName(),
                'value' => $this->session->getSessionId(),
                'lifetime' => $this->session->getCookieLifetime(),
                'path' => $this->session->getCookiePath(),
                'domain' => $this->session->getCookieDomain(),
            ];
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(Category::ADMIN_RESOURCE);
    }
}
