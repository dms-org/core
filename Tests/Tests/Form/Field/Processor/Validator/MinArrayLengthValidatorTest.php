<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\MinArrayLengthValidator;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MinArrayLengthValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        return new MinArrayLengthValidator($this->processedType(), 5);
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
            [[1, 2, 4, 5, 5, 6, 4]],
            [['abc', 'b', 'c', '!!!', '']],
            [range(1, 10)],
        ];
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
            [[], new Message(MinArrayLengthValidator::MESSAGE, ['length' => 5])],
            [[123], new Message(MinArrayLengthValidator::MESSAGE, ['length' => 5])],
            [[1235], new Message(MinArrayLengthValidator::MESSAGE, ['length' => 5])],
            [range(1, 4), new Message(MinArrayLengthValidator::MESSAGE, ['length' => 5])],
        ];
    }
}