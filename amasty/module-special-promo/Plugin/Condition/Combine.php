<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Plugin\Condition;

use Amasty\Rules\Api\ExtendedValidatorInterface;

/**
 * Additional validation for rules with buyxget actions,
 */
class Combine
{
    /**
     * @var ExtendedValidatorInterface
     */
    private $validator;

    public function __construct(
        ExtendedValidatorInterface $validator
    ) {
        $this->validator = $validator;
    }

    public function aroundValidate(
        \Magento\Rule\Model\Condition\Combine $subject,
        \Closure $proceed,
        $type
    ) {
        $validationResult = $this->validator->validate($subject, $type);
        if ($validationResult !== null) {
            return $validationResult;
        }
        //@see \Magento\SalesRule\Model\Rule\Condition\Address::validate
        if ($type instanceof \Magento\Quote\Model\Quote) {
            $type->setQuote($type);
        }

        return $proceed($type);
    }
}
