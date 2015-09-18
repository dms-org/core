<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\GreaterThanOrEqualValidator;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class GreaterThanOrEqualValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        return new GreaterThanOrEqualValidator($this->processedType(), 5);
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
            [5],
            [6],
            [700],
        ];
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
            ['4.99', new Message(GreaterThanOrEqualValidator::MESSAGE, ['value' => 5])],
            [4.99, new Message(GreaterThanOrEqualValidator::MESSAGE, ['value' => 5])],
            [-200, new Message(GreaterThanOrEqualValidator::MESSAGE, ['value' => 5])],
        ];
    }
}