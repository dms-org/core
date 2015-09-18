<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\TypeValidator;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

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