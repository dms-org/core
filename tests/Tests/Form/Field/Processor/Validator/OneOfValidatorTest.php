<?php

namespace Dms\Core\Tests\Form\Field\Processor\Validator;

use Dms\Core\Form\Field\Options\ArrayFieldOptions;
use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Form\Field\Processor\Validator\OneOfValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\Builder\Type;

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
        return new OneOfValidator($this->processedType(), ArrayFieldOptions::fromAssocArray(
                ['foo' => 'Foo', 'bar' => 'Bar', 'baz' => 'Baz']
        ));
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