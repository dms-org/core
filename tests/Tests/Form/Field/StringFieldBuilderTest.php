<?php

namespace Dms\Core\Tests\Form\Field;

use Dms\Core\Form\Field\Builder\Field as Field;
use Dms\Core\Form\Field\Builder\StringFieldBuilder;
use Dms\Core\Form\Field\Processor\EmptyStringToNullProcessor;
use Dms\Core\Form\Field\Processor\TrimProcessor;
use Dms\Core\Form\Field\Processor\TypeProcessor;
use Dms\Core\Form\Field\Processor\Validator\EmailValidator;
use Dms\Core\Form\Field\Processor\Validator\IpAddressValidator;
use Dms\Core\Form\Field\Processor\Validator\MaxLengthValidator;
use Dms\Core\Form\Field\Processor\Validator\MinLengthValidator;
use Dms\Core\Form\Field\Processor\Validator\RequiredValidator;
use Dms\Core\Form\Field\Processor\Validator\StringCharactersValidator;
use Dms\Core\Form\Field\Processor\Validator\UrlValidator;
use Dms\Core\Form\Field\Type\StringType;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\IType;

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

        $this->assertAttributes([StringType::ATTR_TYPE => IType::STRING, StringType::ATTR_MAX_LENGTH => 50], $field);
        $this->assertSame('test', $field->process('test'));
        $this->assertFieldThrows($field, str_repeat('-', 51), [
            new Message(MaxLengthValidator::MESSAGE, [
                'field'      => 'Name',
                'input'      => str_repeat('-', 51),
                'max_length' => 50,
            ]),
        ]);
    }

    public function testMinLength()
    {
        $field = $this->field()->minLength(20)->build();

        $this->assertAttributes([StringType::ATTR_TYPE => IType::STRING, StringType::ATTR_MIN_LENGTH => 20], $field);
        $this->assertSame(str_repeat('-', 51), str_repeat('-', 51));

        $this->assertFieldThrows($field, str_repeat('-', 19), [
            new Message(MinLengthValidator::MESSAGE, [
                'field'      => 'Name',
                'input'      => str_repeat('-', 19),
                'min_length' => 20,
            ]),
        ]);
    }

    public function testMultiline()
    {
        $this->assertAttributes(
            [StringType::ATTR_TYPE => IType::STRING, StringType::ATTR_MULTILINE => true],
            $this->field()->multiline()->build()
        );
    }

    public function testExactLength()
    {
        $this->assertAttributes(
            [StringType::ATTR_TYPE => IType::STRING, StringType::ATTR_EXACT_LENGTH => 25],
            $this->field()->exactLength(25)->build()
        );
    }

    public function testEmail()
    {
        $field = $this->field()->email()->build();

        $this->assertAttributes(
            [StringType::ATTR_TYPE => IType::STRING, StringType::ATTR_STRING_TYPE => StringType::TYPE_EMAIL],
            $field
        );

        $this->assertHasProcessor(new EmailValidator(Type::string()->nullable()), $field);
    }

    public function testUrl()
    {
        $field = $this->field()->url()->build();

        $this->assertAttributes(
            [StringType::ATTR_TYPE => IType::STRING, StringType::ATTR_STRING_TYPE => StringType::TYPE_URL],
            $field
        );

        $this->assertHasProcessor(new UrlValidator(Type::string()->nullable()), $field);
    }

    public function testPassword()
    {
        $field = $this->field()->password()->build();

        $this->assertAttributes(
            [StringType::ATTR_TYPE => IType::STRING, StringType::ATTR_STRING_TYPE => StringType::TYPE_PASSWORD],
            $field
        );
    }

    public function testHtml()
    {
        $field = $this->field()->html()->build();

        $this->assertAttributes(
            [StringType::ATTR_TYPE => IType::STRING, StringType::ATTR_STRING_TYPE => StringType::TYPE_HTML],
            $field
        );
    }

    public function testIpAddress()
    {
        $field = $this->field()->ipAddress()->build();

        $this->assertAttributes(
            [StringType::ATTR_TYPE => IType::STRING, StringType::ATTR_STRING_TYPE => StringType::TYPE_IP_ADDRESS],
            $field
        );

        $this->assertHasProcessor(new IpAddressValidator(Type::string()->nullable()), $field);
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
            new TrimProcessor(" \t\n\r\0\x0B"),
            new RequiredValidator(Type::string()),
            new MaxLengthValidator(Type::string(), 50),
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
            ]),
        ]);

        $this->assertEquals(Type::string(), $field->getProcessedType());
    }

    public function testEmptyStringAsNull()
    {
        $field = $this->field()
            ->trim()
            ->withEmptyStringAsNull()
            ->build();

        $this->assertEquals([
            new TypeProcessor('string'),
            new TrimProcessor(" \t\n\r\0\x0B"),
            new EmptyStringToNullProcessor(Type::string()->nullable()),
        ], $field->getProcessors());

        $this->assertEquals(Type::string()->nullable(), $field->getProcessedType());
    }

    public function testAutocomplete()
    {
        $field = $this->field()
            ->autocomplete(['a', 'b', 'c'])
            ->build();

        $this->assertSame(['a', 'b', 'c'], $field->getType()->get(StringType::ATTR_SUGGESTED_VALUES));
    }

    public function testValidCharacterRanges()
    {
        $field = $this->field()
            ->onlyContainsCharacterRanges([
                'a' => 'z',
                'A' => 'Z',
                '0' => '9',
            ])
            ->build();

        $this->assertEquals([
            new TypeProcessor('string'),
            new StringCharactersValidator(Type::string()->nullable(), [
                'a' => 'z',
                'A' => 'Z',
                '0' => '9',
            ]),
        ], $field->getProcessors());

        $this->assertEquals(Type::string()->nullable(), $field->getProcessedType());
        $this->assertSame([
            'a' => 'z',
            'A' => 'Z',
            '0' => '9',
        ], $field->getType()->get(StringType::ATTR_VALID_CHAR_RANGES));
    }

    public function testCastTo()
    {
        $field = $this->field()->castTo('int')->build();

        $this->assertEquals([
            new TypeProcessor('string'),
            new TypeProcessor('int'),
        ], $field->getProcessors());

        $this->assertSame(null, $field->process(null));
        $this->assertSame(123, $field->process('123'));
        $this->assertSame(Type::int()->nullable(), $field->getProcessedType());
    }

    public function testCastToRequired()
    {
        $field = $this->field()->castTo('int')->required()->build();

        $this->assertEquals([
            new RequiredValidator(Type::mixed()),
            new TypeProcessor('string'),
            new TypeProcessor('int'),
        ], $field->getProcessors());

        $this->assertSame(123, $field->process('123'));
        $this->assertSame(Type::int(), $field->getProcessedType());
    }

    public function testOneOfFieldOptionsWithoutSpecificLoader()
    {
        $field = $this->field()->oneOfOptionsFromCallback(function (string $filter = null) {
            return array_filter([
                'value'         => 'Label',
                'another-value' => 'Another Label',
            ], function ($value) use ($filter) {
                return $filter ? strpos($value, $filter) !== false : true;
            });
        })->build();

        $this->assertSame('value', $field->process('value'));
        $this->assertSame('another-value', $field->process('another-value'));
        $this->assertFailsToProcess(['invalid'], $field);
    }

    public function testOneOfFieldOptionsWithSpecificLoader()
    {
        $field = $this->field()->oneOfOptionsFromCallback(function (string $filter = null) {
            return array_filter([
                'value'         => 'Label',
                'another-value' => 'Another Label',
            ], function ($value) use ($filter) {
                return $filter ? strpos($value, $filter) !== false : true;
            });
        }, function (string $value) {
            return [
                'value'         => 'Label',
                'another-value' => 'Another Label',
            ][$value] ?? null;
        })->build();

        $this->assertSame('value', $field->process('value'));
        $this->assertSame('another-value', $field->process('another-value'));
        $this->assertFailsToProcess(['invalid'], $field);
    }
}