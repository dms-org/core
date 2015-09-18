<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\OneOfValidator;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class OneOfValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        return new OneOfValidator($this->processedType(), ['foo', 'bar', 'baz']);
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
            ['foo'],
            ['bar'],
            ['baz'],
        ];
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
            ['abc', new Message(OneOfValidator::MESSAGE, ['options' => 'foo, bar, baz'])],
            ['foo !', new Message(OneOfValidator::MESSAGE, ['options' => 'foo, bar, baz'])],
            ['bar 1', new Message(OneOfValidator::MESSAGE, ['options' => 'foo, bar, baz'])],
            ['atest', new Message(OneOfValidator::MESSAGE, ['options' => 'foo, bar, baz'])],
        ];
    }
}