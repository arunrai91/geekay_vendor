<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Model\Source;

use Amasty\Acart\Model\ResourceModel\Blacklist;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Validator\EmailAddress as EmailValidator;
use Magento\Framework\Validator\NotEmpty;
use Magento\Framework\Validator\NotEmptyFactory;

class Import extends \Magento\Config\Model\Config\Backend\File
{
    /**
     * @var Blacklist
     */
    protected $blacklistResource;

    /**
     * @var EmailValidator
     */
    private $emailValidator;

    /**
     * @var NotEmpty
     */
    private $notEmpty;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface $requestData,
        Filesystem $filesystem,
        \Amasty\Acart\Model\ResourceModel\Blacklist $blacklistResource,
        EmailValidator $emailValidator,
        NotEmptyFactory $notEmptyFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $uploaderFactory,
            $requestData,
            $filesystem,
            $resource,
            $resourceCollection,
            $data
        );
        $this->blacklistResource = $blacklistResource;
        $this->emailValidator = $emailValidator;
        $this->notEmpty = $notEmptyFactory->create(['options' => []]);
    }

    public function beforeSave()
    {
        return $this;
    }

    public function save()
    {
        $tmpName = $this->_requestData->getTmpName($this->getPath());
        $directoryRead = $this->_filesystem->getDirectoryRead(DirectoryList::SYS_TMP);
        $file = $directoryRead->openFile($directoryRead->getRelativePath($tmpName));
        $emails = [];

        while (($csvLine = $file->readCsv()) !== false) {
            foreach ($csvLine as $email) {
                if ($this->notEmpty->isValid($email)
                    && $this->emailValidator->isValid($email)
                ) {
                    $emails[]['customer_email'] = $email;
                }
            }
        }

        if ($emails) {
            $this->blacklistResource->saveImportData($emails);
        }

        return $this;
    }

    protected function _getAllowedExtensions()
    {
        return ['csv', 'txt'];
    }
}
