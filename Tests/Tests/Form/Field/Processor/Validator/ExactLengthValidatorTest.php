<?php

namespace Dms\Core\Tests\Form\Field\Processor\Validator;

use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Form\Field\Processor\Validator\ExactLengthValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ExactLengthValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        return new ExactLengthValidator($this->processedType(), 4);
    }

    /**
     * @inheritDoc
     */
    protected function processedType()
    {
        return Type::mixed();
    }

    /**
     * @return array[]
     */
    public function successTests()
    {
        return [
            [null],
            ['abce'],
            [1234],
            ['1234'],
            ['    '],
        ];
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
            ['', new Message(ExactLengthValidator::MESSAGE, ['length' => 4])],
            ['a', new Message(ExactLengthValidator::MESSAGE, ['length' => 4])],
            ['ab', new Message(ExactLengthValidator::MESSAGE, ['length' => 4])],
            ['sfdfdf', new Message(ExactLengthValidator::MESSAGE, ['length' => 4])],
        ];
    }
}