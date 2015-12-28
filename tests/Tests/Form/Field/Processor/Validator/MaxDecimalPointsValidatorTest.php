<?php

namespace Dms\Core\Tests\Form\Field\Processor\Validator;

use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Form\Field\Processor\Validator\MaxDecimalPointsValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MaxDecimalPointsValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        return new MaxDecimalPointsValidator($this->processedType(), 5);
    }

    /**
     * @inheritDoc
     */
    protected function processedType()
    {
        return Type::float()->nullable();
    }

    /**
     * @return array[]
     */
    public function successTests()
    {
        return [
                [null],
                [1.0],
                [1.01],
                [1.001],
                [1.0011],
                [1.00111],
                [9.43543],
        ];
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
                [
                        0.000001,
                        new Message(MaxDecimalPointsValidator::MESSAGE, ['max_decimal_points' => 5])
                ],
        ];
    }
}