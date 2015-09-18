<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\ExactArrayLengthValidator;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ExactArrayLengthValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        return new ExactArrayLengthValidator($this->processedType(), 4);
    }

    /**
     * @inheritDoc
     */
    protected function processedType()
    {
        return Type::arrayOf(Type::mixed())->nullable();
    }

    /**
     * @return array[]
     */
    public function successTests()
    {
        return [
            [null],
            [[1, 2, 4, 5]],
            [['abc', 'b', 'c', '!!!']],
        ];
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
            [[], new Message(ExactArrayLengthValidator::MESSAGE, ['length' => 4])],
            [[123], new Message(ExactArrayLengthValidator::MESSAGE, ['length' => 4])],
            [[1235], new Message(ExactArrayLengthValidator::MESSAGE, ['length' => 4])],
            [range(1, 5), new Message(ExactArrayLengthValidator::MESSAGE, ['length' => 4])],
        ];
    }
}