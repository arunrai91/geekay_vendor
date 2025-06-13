<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Test\Integration\Block\Catalog\Product\ProductList;

use Amasty\ShopbyBrand\Block\Catalog\Product\ProductList\MoreFrom;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @see MoreFrom
 * @magentoDataFixture Magento/Catalog/_files/products_with_dropdown_attribute.php
 */
class TitleMoreFromTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var MoreFrom
     */
    private $moreFrom;

    public function setup(): void
    {
        $this->moreFrom = Bootstrap::getObjectManager()->get(MoreFrom::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(
            ProductRepositoryInterface::class
        );
    }

    /**
     * @covers MoreFrom::getTitle
     *
     * @magentoConfigFixture current_store amshopby_brand/general/attribute_code dropdown_attribute
     * @magentoConfigFixture current_store amshopby_brand/more_from_brand/title {brand_name} test {brand_name}
     */
    public function testGetTitle()
    {
        $product = $this->productRepository->get('simple_op_1');
        $this->moreFrom->setData('product', $product);
        $resultOrigMethod = $this->moreFrom->getTitle();

        $this->assertEquals('Option 1 test Option 1', $resultOrigMethod);
    }
}
