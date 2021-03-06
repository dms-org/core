<?php

namespace Dms\Core\Tests\Form\Field\Processor\Validator;

use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Form\Field\Processor\Validator\LessThanOrEqualValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LessThanOrEqualValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        return new LessThanOrEqualValidator($this->processedType(), 5);
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
            [4],
            [4.99],
            [5],
            [-1023.4],
        ];
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
            [5.0001, new Message(LessThanOrEqualValidator::MESSAGE, ['value' => 5])],
            [6, new Message(LessThanOrEqualValidator::MESSAGE, ['value' => 5])],
            [200, new Message(LessThanOrEqualValidator::MESSAGE, ['value' => 5])],
        ];
    }
}