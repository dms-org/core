<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\MinLengthValidator;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MinLengthValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        return new MinLengthValidator($this->processedType(), 4);
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
            [12345],
            ['123543454'],
            ['     '],
        ];
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
            ['', new Message(MinLengthValidator::MESSAGE, ['min_length' => 4])],
            ['a', new Message(MinLengthValidator::MESSAGE, ['min_length' => 4])],
            ['ab', new Message(MinLengthValidator::MESSAGE, ['min_length' => 4])],
            ['sff', new Message(MinLengthValidator::MESSAGE, ['min_length' => 4])],
        ];
    }
}