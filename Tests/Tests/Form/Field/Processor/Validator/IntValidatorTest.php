<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\IntValidator;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IntValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        return new IntValidator($this->processedType());
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
            ['752873'],
            ['-44548183'],
        ];
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
            ['gfdgdfg', new Message(IntValidator::MESSAGE)],
            ['asf1343', new Message(IntValidator::MESSAGE)],
            ['!$%46t', new Message(IntValidator::MESSAGE)],
            ['1.5', new Message(IntValidator::MESSAGE)],
        ];
    }
}