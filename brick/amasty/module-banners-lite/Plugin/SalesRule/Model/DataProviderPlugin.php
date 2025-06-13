<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Banners Lite for Magento 2 (System)
 */

namespace Amasty\BannersLite\Plugin\SalesRule\Model;

use Amasty\BannersLite\Api\Data\BannerInterface;
use Amasty\BannersLite\Model\Banner;
use Amasty\BannersLite\Model\ImageProcessor;
use Magento\SalesRule\Model\Data\Rule;
use Magento\SalesRule\Model\Rule\DataProvider;

class DataProviderPlugin
{
    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    public function __construct(ImageProcessor $imageProcessor)
    {
        $this->imageProcessor = $imageProcessor;
    }

    /**
     * Convert Promo Banners data to Array
     *
     * @param DataProvider $subject
     * @param array|null $result
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetData(DataProvider $subject, ?array $result): ?array
    {
        if (is_array($result)) {
            foreach ($result as &$item) {
                if (isset($item[BannerInterface::EXTENSION_ATTRIBUTES_KEY][BannerInterface::EXTENSION_CODE])) {
                    $ruleId = $item[Rule::KEY_RULE_ID] ?? null;
                    $banners = &$item[BannerInterface::EXTENSION_ATTRIBUTES_KEY][BannerInterface::EXTENSION_CODE];
                    foreach ($banners as $key => $banner) {
                        if ($banner instanceof BannerInterface) {
                            $banners[$key] = $this->prepareBannerData($banner, $ruleId, $key);
                        }
                    }
                }
            }
        }

        return $result;
    }

    private function prepareBannerData(Banner $banner, ?int $ruleId, int $bannerPosition): array
    {
        $result = $banner->getData();
        $bannerImage = (string)($result[BannerInterface::BANNER_IMAGE] ?? null);

        if ($bannerImage) {
            $result[BannerInterface::BANNER_IMAGE] = [
                0 => [
                    'name' => $bannerImage,
                    'url' => $this->imageProcessor->getBannerImageUrl($bannerImage)
                ]
            ];
        }

        if (empty($result) && $ruleId) {
            $emptyArray = [ //compatibility with old Amasty_Rules
                BannerInterface::BANNER_ALT => "",
                BannerInterface::BANNER_HOVER_TEXT => ""
            ];
            $result += [BannerInterface::BANNER_TYPE => $bannerPosition, BannerInterface::SALESRULE_ID => $ruleId];
            $result = array_merge($result, $emptyArray);
        }

        return $result;
    }
}
