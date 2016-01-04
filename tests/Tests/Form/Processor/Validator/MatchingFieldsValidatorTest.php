<?php

namespace Dms\Core\Tests\Form\Processor\Validator;

use Dms\Core\Form\Field\Field;
use Dms\Core\Form\Field\Type\StringType;
use Dms\Core\Form\Processor\FormValidator;
use Dms\Core\Form\Processor\Validator\MatchingFieldsValidator;
use Dms\Core\Language\Message;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MatchingFieldsValidatorTest extends FormValidatorTest
{
    /**
     * @var MatchingFieldsValidator
     */
    protected $validator;

    /**
     * @return MatchingFieldsValidator
     */
    protected function validator()
    {
        return new MatchingFieldsValidator(
                new Field('one', 'One', new StringType(), []),
                new Field('two', 'Two', new StringType(), [])
        );
    }

    /**
     * @return string[]
     */
    public function fieldNameMap()
    {
        return ['one' => 'abc', 'two' => 'def'];
    }

    /**
     * @return array[]
     */
    public function successTests()
    {
        return [
                [[]],
                [['one' => null, 'two' => null]],
                [['one' => 'abc', 'two' => 'abc']],
                [['foo' => 'bar', 'one' => 123, 'two' => 123]],
                [['one' => new \DateTime('2000-01-01 00:00:00'), 'two' => new \DateTime('2000-01-01 00:00:00')]],
        ];
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
                [['one' => 'bar', 'two' => 'baz'], new Message(MatchingFieldsValidator::MESSAGE, ['field1' => 'One', 'field2' => 'Two'])],
                [['one' => 123, 'two' => '123'], new Message(MatchingFieldsValidator::MESSAGE, ['field1' => 'One', 'field2' => 'Two'])],
        ];
    }

    public function testGetters()
    {
        $this->assertSame('one', $this->validator->getField1()->getName());
        $this->assertSame('two', $this->validator->getField2()->getName());
    }

    public function testGettersAfterWithNames()
    {
        $this->validator = $this->validator->withFieldNames($this->fieldNameMap());

        $this->assertSame('abc', $this->validator->getField1()->getName());
        $this->assertSame('def', $this->validator->getField2()->getName());
    }
}