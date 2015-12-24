<?php

namespace Dms\Core\Tests\Form\Field\Processor\Validator;

use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Form\Field\Processor\Validator\TypeValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TypeValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        return new TypeValidator($this->processedType());
    }

    /**
     * @inheritDoc
     */
    protected function processedType()
    {
        return Type::string();
    }

    /**
     * @return array[]
     */
    public function successTests()
    {
        return [
            [null],
            [''],
            ['dsfdf'],
            ['124'],
            [' '],
        ];
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
            [0, new Message(TypeValidator::MESSAGE, ['type' => 'string'])],
            [0.0, new Message(TypeValidator::MESSAGE, ['type' => 'string'])],
            [true, new Message(TypeValidator::MESSAGE, ['type' => 'string'])],
            [[], new Message(TypeValidator::MESSAGE, ['type' => 'string'])],
            [new \stdClass(), new Message(TypeValidator::MESSAGE, ['type' => 'string'])],
        ];
    }
}