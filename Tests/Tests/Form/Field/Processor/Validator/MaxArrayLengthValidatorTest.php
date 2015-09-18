<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\MaxArrayLengthValidator;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MaxArrayLengthValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        return new MaxArrayLengthValidator($this->processedType(), 3);
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
            [[]],
            [['abc', 'b', 'c']],
            [range(1, 2)],
        ];
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
            [[1, 2, 4, 5], new Message(MaxArrayLengthValidator::MESSAGE, ['length' => 3])],
            [range(1, 4), new Message(MaxArrayLengthValidator::MESSAGE, ['length' => 3])],
            [range(1, 50, 2), new Message(MaxArrayLengthValidator::MESSAGE, ['length' => 3])],
        ];
    }
}