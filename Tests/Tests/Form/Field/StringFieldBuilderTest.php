<?php

namespace Iddigital\Cms\Core\Tests\Form\Field;

use Iddigital\Cms\Core\Form\Field\Builder\Field as Field;
use Iddigital\Cms\Core\Form\Field\Processor\TrimProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\TypeProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\MaxLengthValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\MinLengthValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\RequiredValidator;
use Iddigital\Cms\Core\Form\Field\Builder\StringFieldBuilder;
use Iddigital\Cms\Core\Form\Field\Type\StringType;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Model\Type\ScalarType;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class StringFieldBuilderTest extends FieldBuilderTestBase
{
    /**
     * @param string $name
     * @param string $label
     *
     * @return StringFieldBuilder
     */
    protected function field($name = 'name', $label = 'Name')
    {
        return Field::name($name)->label($label)->string();
    }

    public function testMaxLength()
    {
        $field = $this->field()->maxLength(50)->build();

        $this->assertAttributes([StringType::ATTR_MAX_LENGTH => 50], $field);
        $this->assertSame('test', $field->process('test'));
        $this->assertFieldThrows($field, str_repeat('-', 51), [
            new Message(MaxLengthValidator::MESSAGE, [
                'field'      => 'Name',
                'input'      => str_repeat('-', 51),
                'max_length' => 50,
            ])
        ]);
    }

    public function testMinLength()
    {
        $field = $this->field()->minLength(20)->build();

        $this->assertAttributes([StringType::ATTR_MIN_LENGTH => 20], $field);
        $this->assertSame(str_repeat('-', 51), str_repeat('-', 51));

        $this->assertFieldThrows($field, str_repeat('-', 19), [
            new Message(MinLengthValidator::MESSAGE, [
                'field'      => 'Name',
                'input'      => str_repeat('-', 19),
                'min_length' => 20,
            ])
        ]);
    }

    public function testExactLength()
    {
        $this->assertAttributes(
            [StringType::ATTR_MIN_LENGTH => 25, StringType::ATTR_MAX_LENGTH => 25],
            $this->field()->exactLength(25)->build()
        );
    }

    public function testEmail()
    {
        $this->assertAttributes(
            [StringType::ATTR_TYPE => StringType::TYPE_EMAIL],
            $this->field()->email()->build()
        );
    }

    public function testUrl()
    {
        $this->assertAttributes(
            [StringType::ATTR_TYPE => StringType::TYPE_URL],
            $this->field()->url()->build()
        );
    }

    public function testPassword()
    {
        $this->assertAttributes(
                [StringType::ATTR_TYPE => StringType::TYPE_PASSWORD],
                $this->field()->password()->build()
        );
    }

    public function testHtml()
    {
        $this->assertAttributes(
                [StringType::ATTR_TYPE => StringType::TYPE_HTML],
                $this->field()->html()->build()
        );
    }

    public function testTextFieldWithProcessors()
    {
        $field = $this->field()
            ->trim()
            ->maxLength(50)
            ->required()
            ->build();

        $this->assertEquals([
                new TypeProcessor('string'),
                new TrimProcessor(),
                new MaxLengthValidator(Type::string()->nullable(), 50),
                new RequiredValidator(Type::string()->nullable())
        ], $field->getProcessors());

        $this->assertSame('name', $field->getName());
        $this->assertSame('Name', $field->getLabel());
        $this->assertSame(50, $field->getType()->get(StringType::ATTR_MAX_LENGTH));
        $this->assertSame('John Smith', $field->process('John Smith '));
        $this->assertFieldThrows($field, str_repeat('-', 51), [
            new Message(MaxLengthValidator::MESSAGE, [
                'field'      => 'Name',
                'input'      => str_repeat('-', 51),
                'max_length' => 50,
            ])
        ]);

        $this->assertEquals(Type::string(), $field->getProcessedType());
    }
}