<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Banners Lite for Magento 2 (System)
 */

namespace Amasty\BannersLite\Model\ResourceModel;

use Amasty\BannersLite\Api\Data\BannerInterface;
use Amasty\BannersLite\Model\ImageProcessor;
use Amasty\Base\Model\Serializer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use \Magento\Framework\Model\ResourceModel\Db\Context;

class Banner extends AbstractDb
{
    public const TABLE_NAME = 'amasty_banners_lite_banner_data';

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    public function __construct(
        Serializer $serializerBase, //@deprecated backward compatibility
        ImageProcessor $imageProcessor,
        Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->imageProcessor = $imageProcessor;
    }

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, BannerInterface::ENTITY_ID);
    }

    /**
     * @param \Amasty\BannersLite\Model\Banner $object
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $bannerImage = $object->getBannerImage();
        $validName = null;
        if ($bannerImage) {
            try {
                $validName = $this->imageProcessor->moveFileFromTmp($bannerImage);
            } catch (LocalizedException $exception) {// file already was moved from tmp
                if ($object->isObjectNew() === true) {//duplicated rule
                    if (!$validName) {
                        $validName = $this->imageProcessor->copyFile($bannerImage);
                    }
                } else {
                    $validName = $bannerImage;//just re-saving
                }
            }
        }
        $object->setBannerImage($validName);

        return parent::_beforeSave($object);
    }
}
