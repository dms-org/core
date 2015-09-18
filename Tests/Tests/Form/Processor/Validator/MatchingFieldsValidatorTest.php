<?php

namespace Iddigital\Cms\Core\Tests\Form\Processor\Validator;

use Iddigital\Cms\Core\Form\Field\Field;
use Iddigital\Cms\Core\Form\Field\Type\StringType;
use Iddigital\Cms\Core\Form\Processor\FormValidator;
use Iddigital\Cms\Core\Form\Processor\Validator\MatchingFieldsValidator;
use Iddigital\Cms\Core\Language\Message;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MatchingFieldsValidatorTest extends FormValidatorTest
{

    /**
     * @return FormValidator
     */
    protected function validator()
    {
        return new MatchingFieldsValidator(
                new Field('one', 'One', new StringType(), []),
                new Field('two', 'Two', new StringType(), [])
        );
    }

    /**
     * @return array[]
     */
    public function successTests()
    {
        return [
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
                [[], new Message(MatchingFieldsValidator::MESSAGE, ['field1' => 'One', 'field2' => 'Two'])],
                [['one' => 'bar', 'two' => 'baz'], new Message(MatchingFieldsValidator::MESSAGE, ['field1' => 'One', 'field2' => 'Two'])],
                [['one' => 123, 'two' => '123'], new Message(MatchingFieldsValidator::MESSAGE, ['field1' => 'One', 'field2' => 'Two'])],
        ];
    }
}