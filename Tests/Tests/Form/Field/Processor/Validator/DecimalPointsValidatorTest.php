<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\DecimalPointsValidator;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DecimalPointsValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        return new DecimalPointsValidator($this->processedType(), 3, 5);
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
            [1.0], // Should allow if .0
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
            [0.5, new Message(DecimalPointsValidator::MESSAGE_MIN, ['min_decimal_points' => 3])],
            [0.01, new Message(DecimalPointsValidator::MESSAGE_MIN, ['min_decimal_points' => 3])],
            [
                0.000001,
                new Message(DecimalPointsValidator::MESSAGE_MAX, ['max_decimal_points' => 5])
            ],
        ];
    }
}