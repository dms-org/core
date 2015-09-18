<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\FloatValidator;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FloatValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        return new FloatValidator($this->processedType());
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
            [1],
            [0],
            ['1'],
            ['0'],
            ['1.1'],
            ['0.1'],
            ['324320.324321'],
            ['-65678.34554'],
        ];
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
            ['gfdgdfg', new Message(FloatValidator::MESSAGE)],
            ['asf1343', new Message(FloatValidator::MESSAGE)],
            ['!$%46t', new Message(FloatValidator::MESSAGE)],
        ];
    }
}