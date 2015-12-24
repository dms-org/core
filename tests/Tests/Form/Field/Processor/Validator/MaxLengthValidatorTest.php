<?php

namespace Dms\Core\Tests\Form\Field\Processor\Validator;

use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Form\Field\Processor\Validator\MaxLengthValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MaxLengthValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        return new MaxLengthValidator($this->processedType(), 4);
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
            ['dsd'],
            ['af'],
            [' '],
            [''],
        ];
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
            ['45435', new Message(MaxLengthValidator::MESSAGE, ['max_length' => 4])],
            ['5454!', new Message(MaxLengthValidator::MESSAGE, ['max_length' => 4])],
            ['sdgf!', new Message(MaxLengthValidator::MESSAGE, ['max_length' => 4])],
            ['fdfdffdsdf', new Message(MaxLengthValidator::MESSAGE, ['max_length' => 4])],
        ];
    }
}