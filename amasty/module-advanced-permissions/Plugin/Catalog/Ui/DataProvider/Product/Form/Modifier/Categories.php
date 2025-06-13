<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Plugin\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Framework\AuthorizationInterface;

class Categories
{
    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    public function __construct(
        AuthorizationInterface $authorization
    ) {
        $this->authorization = $authorization;
    }

    public function afterModifyMeta(\Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Categories $subject, $result)
    {
        $canCreateCategories = $this->authorization
            ->isAllowed('Amasty_Rolepermissions::create_categories');

        if (!$canCreateCategories) {
            unset(
                $result['product-details']['children']['container_category_ids']['children']['create_category_button']
            );
        }

        return $result;
    }
}
