<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\BoolValidator;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\MixedType;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class BoolValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        return new BoolValidator($this->processedType());
    }

    /**
     * @inheritDoc
     */
    protected function processedType()
    {
        return new MixedType();
    }

    /**
     * @return array[]
     */
    public function successTests()
    {
        return [
            [null],
            [true],
            [false],
            [1],
            [0],
            ['1'],
            ['0'],
            ['yes'],
            ['no'],
        ];
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
            [-1, new Message(BoolValidator::MESSAGE)],
            [2, new Message(BoolValidator::MESSAGE)],
            [new \stdClass(), new Message(BoolValidator::MESSAGE)],
            [[], new Message(BoolValidator::MESSAGE)],
            [0.5, new Message(BoolValidator::MESSAGE)],
            ['dsfdsfdsf', new Message(BoolValidator::MESSAGE)],
        ];
    }
}