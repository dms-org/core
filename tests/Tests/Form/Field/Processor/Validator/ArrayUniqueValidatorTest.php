<?php

namespace Dms\Core\Tests\Form\Field\Processor\Validator;

use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Form\Field\Processor\Validator\ArrayUniqueValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\ArrayType;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayUniqueValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        return new ArrayUniqueValidator($this->processedType());
    }

    /**
     * @return ArrayType
     */
    protected function processedType()
    {
        return Type::arrayOf(Type::int())->nullable();
    }

    /**
     * @return array[]
     */
    public function successTests()
    {
        return [
                [null],
                [[100, 99]],
                [[]],
                [range(30, 50, 2)],
                [[-102, 6]],
        ];
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
                [[1, 1], new Message(ArrayUniqueValidator::MESSAGE)],
                [[1, 2, 3, 4, 2], new Message(ArrayUniqueValidator::MESSAGE)],
                [[0, 0, 0, 0], new Message(ArrayUniqueValidator::MESSAGE)],
                [[100, 10, 10], new Message(ArrayUniqueValidator::MESSAGE)],
        ];
    }
}