<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\RequiredValidator;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RequiredValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        return new RequiredValidator($this->processedType());
    }

    /**
     * @inheritDoc
     */
    protected function processedType()
    {
        return Type::mixed()->nonNullable();
    }

    /**
     * @return array[]
     */
    public function successTests()
    {
        return [
                [1],
                [true],
                ['foo'],
                [' '],
                ['a'],
                [[1]],
                ['0'],
                [0],
                [0.0],
        ];
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
                [null, new Message(RequiredValidator::MESSAGE)],
                ['', new Message(RequiredValidator::MESSAGE)],
                [false, new Message(RequiredValidator::MESSAGE)],
        ];
    }
}