<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Controller\Adminhtml\Feed;

use Amasty\Base\Model\MagentoVersion;
use Amasty\Feed\Api\FeedRepositoryInterface;
use Amasty\Feed\Controller\Adminhtml\AbstractFeed;
use Magento\Backend\App\Action;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Io\Ftp;
use Magento\Framework\Filesystem\Io\Sftp;
use Magento\Framework\Math\Random;

class Connection extends AbstractFeed
{
    public const SOURCE_LOCAL_FILE = 1;
    public const SOURCE_STRING = 2;

    /**
     * @var Ftp
     */
    private $ftp;

    /**
     * @var Sftp
     */
    private $sftp;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var FeedRepositoryInterface
     */
    private $feedRepository;

    /**
     * @var Random
     */
    private $random;

    /**
     * @var MagentoVersion
     */
    private $magentoVersion;

    public function __construct(
        Action\Context $context,
        Ftp $ftp,
        Sftp $sftp,
        ?ProductMetadataInterface $metadata,  // @deprecated
        EncryptorInterface $encryptor,
        Random $random,
        FeedRepositoryInterface $feedRepository,
        ?MagentoVersion $magentoVersion = null // TODO move to not optional
    ) {
        parent::__construct($context);
        $this->ftp = $ftp;
        $this->sftp = $sftp;
        $this->encryptor = $encryptor;
        $this->random = $random;
        $this->feedRepository = $feedRepository;
        // OM for backward compatibility
        $this->magentoVersion = $magentoVersion ?? ObjectManager::getInstance()->get(MagentoVersion::class);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        //TODO use \Amasty\Feed\Model\Filesystem\Ftp
        try {
            $this->testConnection();
            $resultJson->setData(__('Success!'));
        } catch (\Exception $error) {
            $resultJson->setData(['type' => 'error', 'message' => __($error->getMessage())]);
        }

        return $resultJson;
    }

    /**
     * @throws LocalizedException
     */
    private function testConnection()
    {
        $params = $this->getRequest()->getParams();
        if (!$params) {
            throw new LocalizedException(__('Request params is empty'));
        }

        //Generate random .tmp file name to check write permissions
        $this->fileName = $this->random->getUniqueHash() . '.tmp';

        if (preg_match('/^\*+$/', $params['pass'])) {
            $pass = $this->feedRepository->getById($params['feed_id'])->getDeliveryPassword();
            $params['pass'] = $this->encryptor->decrypt($pass);
        }
        if ($params['proto'] === 'ftp') {
            $this->testFtpConnection($params);
        } elseif ($params['proto'] === 'sftp') {
            $this->testSftpConnection($params);
        } else {
            throw new LocalizedException(__('Invalid protocol'));
        }
    }

    /**
     * @param array $params
     *
     * @throws LocalizedException
     */
    private function testFtpConnection($params)
    {
        if (strpos($params['host'], ':') !== false) {
            list($host, $port) = explode(':', $params['host'], 2);
        } else {
            $host = $params['host'];
            $port = null;
        }

        $this->ftp->open(
            [
                'host' => $host,
                'port' => $port,
                'user' => $params['user'],
                'password' => $params['pass'],
                'passive' => $params['mode'],
                'path' => $params['path']
            ]
        );

        if (!$this->ftp->write($this->fileName, (string)__('Amasty Feed test connection file!'))) {
            $this->ftp->close();
            throw new LocalizedException(__('No write permissions'));
        }
        $this->ftp->rm($this->fileName);

        $this->ftp->close();
    }

    /**
     * @param array $params
     *
     * @throws LocalizedException
     */
    private function testSftpConnection($params)
    {
        if (version_compare($this->magentoVersion->get(), '2.2.0', '<')) {
            /** Fix for Magento <2.2.0 versions @see https://github.com/magento/magento2/issues/9016 */
            define('NET_SFTP_LOCAL_FILE', self::SOURCE_LOCAL_FILE);
            define('NET_SFTP_STRING', self::SOURCE_STRING);
        }

        $this->sftp->open(
            [
                'host' => $params['host'],
                'username' => $params['user'],
                'password' => $params['pass']
            ]
        );

        $path = $this->sftp->cd($params['path']);
        if (!$path) {
            $this->sftp->close();
            throw new LocalizedException(__('Invalid path'));
        }

        if (!$this->sftp->write($this->fileName, __('Amasty Feed test connection file!'))) {
            $this->sftp->close();
            throw new LocalizedException(__('No write permissions'));
        }
        $this->sftp->rm($this->fileName);

        $this->sftp->close();
    }
}
