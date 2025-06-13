<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Model\Rule\Validator;

class ValidatorPool implements \Amasty\Rules\Api\ExtendedValidatorInterface
{
    /**
     * @var array
     */
    private $validatorsData;

    public function __construct(array $validatorsData)
    {
        $this->validatorsData = $validatorsData;
    }

    /**
     * @param $combineCondition
     * @param $type
     *
     * @return bool|null
     */
    public function validate($combineCondition, $type)
    {
        foreach ($this->getValidators() as $validator) {
            $resultValidation = $validator->validate($combineCondition, $type);
            if ($resultValidation !== null) {
                return $resultValidation;
            }
        }

        return null;
    }

    /**
     * @return \Amasty\Rules\Api\ExtendedValidatorInterface[]
     */
    private function getValidators()
    {
        usort(
            $this->validatorsData,
            function ($a, $b) {
                return $a['sortOrder'] <=> $b['sortOrder'];
            }
        );

        $validators = array_map(
            function ($validatorData) {
                return $validatorData['object'];
            },
            $this->validatorsData
        );

        return $validators;
    }

    public function _resetState(): void
    {
        $this->validatorsData = null;
    }
}
