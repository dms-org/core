<?php

namespace Iddigital\Cms\Core\Tests\Form\Field;

use Iddigital\Cms\Core\File\IUploadedFile;
use Iddigital\Cms\Core\File\IUploadedImage;
use Iddigital\Cms\Core\Form\Field\Builder\Field as Field;
use Iddigital\Cms\Core\Form\Field\Options\ArrayFieldOptions;
use Iddigital\Cms\Core\Form\Field\Options\EntityIdOptions;
use Iddigital\Cms\Core\Form\Field\Options\FieldOption;
use Iddigital\Cms\Core\Form\Field\Processor\ArrayAllProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\BoolProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\DateTimeProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\DefaultValueProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\EntityArrayLoaderProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\EntityLoaderProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\EnumProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\InnerFormProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\TypeProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\BoolValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\DateFormatValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\EntityIdArrayValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\EntityIdValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\OneOfValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\RequiredValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\TypeValidator;
use Iddigital\Cms\Core\Form\Field\Type\ArrayOfEntityIdsType;
use Iddigital\Cms\Core\Form\Field\Type\ArrayOfType;
use Iddigital\Cms\Core\Form\Field\Type\CustomType;
use Iddigital\Cms\Core\Form\Field\Type\DateTimeType;
use Iddigital\Cms\Core\Form\Field\Type\DateType;
use Iddigital\Cms\Core\Form\Field\Type\EntityIdType;
use Iddigital\Cms\Core\Form\Field\Type\FieldType;
use Iddigital\Cms\Core\Form\Field\Type\FileType;
use Iddigital\Cms\Core\Form\Field\Type\ImageType;
use Iddigital\Cms\Core\Form\Field\Type\InnerFormType;
use Iddigital\Cms\Core\Form\Field\Type\ScalarType;
use Iddigital\Cms\Core\Form\Field\Type\StringType;
use Iddigital\Cms\Core\Form\Field\Type\TimeType;
use Iddigital\Cms\Core\Form\IForm;
use Iddigital\Cms\Core\Model\EntityCollection;
use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Model\Type\Builder\Type as PhpType;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Model\Type\ObjectType;
use Iddigital\Cms\Core\Tests\Form\Field\Processor\Fixtures\StatusEnum;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FieldBuilderTest extends FieldBuilderTestBase
{
    /**
     * @param string $name
     * @param string $label
     *
     * @return Field
     */
    protected function field($name = 'name', $label = 'Name')
    {
        return Field::name($name)->label($label);
    }

    public function testFieldNameAndLabel()
    {
        $field = $this->field()->string()->build();

        $this->assertSame('name', $field->getName());
        $this->assertSame('Name', $field->getLabel());
    }

    public function testInitialValue()
    {
        $field = $this->field()->value('abcdef')->string()->build();

        $this->assertSame('abcdef', $field->getInitialValue());
    }

    public function testRequiredField()
    {
        $field = $this->field()->string()->required()->build();

        $this->assertAttributes([FieldType::ATTR_REQUIRED => true], $field);
        $this->assertHasProcessor(new RequiredValidator(PhpType::string()->nullable()), $field);
        $this->assertEquals(PhpType::string(), $field->getProcessedType());
    }

    public function testDefaultValueField()
    {
        $field = $this->field()->string()->defaultTo('abc')->build();

        $this->assertAttributes([FieldType::ATTR_DEFAULT => 'abc'], $field);
        $this->assertHasProcessor(new DefaultValueProcessor(PhpType::string()->nullable(), 'abc'), $field);
        $this->assertEquals(PhpType::string(), $field->getProcessedType());
    }

    public function testOneOfOptionsField()
    {
        $field = $this->field()->string()->oneOf(['hi' => 'Hi', 'bye' => 'Bye'])->build();

        $this->assertAttributes(
                [FieldType::ATTR_OPTIONS => ArrayFieldOptions::fromAssocArray(['hi' => 'Hi', 'bye' => 'Bye'])],
                $field
        );
        $this->assertHasProcessor(new OneOfValidator(PhpType::string()->nullable(), ['hi', 'bye']), $field);
        $this->assertEquals(PhpType::string()->nullable(), $field->getProcessedType());
    }

    public function testStringField()
    {
        $field = $this->field()->string()->build();
        $this->assertScalarType(ScalarType::STRING, $field);
        $this->assertHasProcessor(new TypeProcessor('string'), $field);
        $this->assertEquals(PhpType::string()->nullable(), $field->getProcessedType());
    }

    public function testIntField()
    {
        $field = $this->field()->int()->build();
        $this->assertScalarType(ScalarType::INT, $field);
        $this->assertHasProcessor(new TypeProcessor('int'), $field);
        $this->assertEquals(PhpType::int()->nullable(), $field->getProcessedType());
    }

    public function testBoolField()
    {
        $field = $this->field()->bool()->build();
        $this->assertScalarType(ScalarType::BOOL, $field);
        $this->assertHasProcessor(new BoolProcessor(), $field);
        $this->assertEquals(PhpType::bool()->nullable(), $field->getProcessedType());
    }

    public function testDecimalField()
    {
        $field = $this->field()->decimal()->build();
        $this->assertScalarType(ScalarType::FLOAT, $field);
        $this->assertHasProcessor(new TypeProcessor('float'), $field);
        $this->assertEquals(PhpType::float()->nullable(), $field->getProcessedType());
    }

    public function testArrayOfField()
    {
        $field = $this->field()->arrayOf(Field::element()->bool())->build();

        /** @var ArrayOfType $type */
        $type = $field->getType();
        $this->assertInstanceOf(ArrayOfType::class, $type);
        $this->assertInstanceOf(ScalarType::class, $type->getElementType());
        $this->assertSame(ScalarType::BOOL, $type->getElementType()->getType());
        $this->assertHasProcessor(new TypeValidator(PhpType::arrayOf(PhpType::mixed())->nullable()), $field);
        $this->assertHasProcessor(new ArrayAllProcessor([new BoolValidator(PhpType::mixed()), new BoolProcessor()]), $field);
        $this->assertEquals(PhpType::arrayOf(PhpType::bool()->nullable())->nullable(), $field->getProcessedType());
    }

    public function testEntityField()
    {
        $entities = new EntityCollection(IEntity::class);
        $field    = $this->field()->entityFrom($entities)->build();

        /** @var EntityIdType $type */
        $type = $field->getType();
        $this->assertInstanceOf(EntityIdType::class, $type);
        $this->assertInstanceOf(EntityIdOptions::class, $type->getOptions());
        $this->assertSame($entities, $type->getOptions()->getEntities());
        $this->assertHasProcessor(new EntityIdValidator(PhpType::int()->nullable(), $entities), $field);
        $this->assertHasProcessor(new EntityLoaderProcessor($entities), $field);
        $this->assertEquals(PhpType::object(IEntity::class)->nullable(), $field->getProcessedType());
    }

    public function testEntityIdField()
    {
        $entities = new EntityCollection(IEntity::class);
        $field    = $this->field()->entityIdFrom($entities)->build();

        /** @var EntityIdType $type */
        $type = $field->getType();
        $this->assertInstanceOf(EntityIdType::class, $type);
        $this->assertInstanceOf(EntityIdOptions::class, $type->getOptions());
        $this->assertSame($entities, $type->getOptions()->getEntities());
        $this->assertHasProcessor(new EntityIdValidator(PhpType::int()->nullable(), $entities), $field);
        $this->assertEquals(PhpType::int()->nullable(), $field->getProcessedType());
    }

    public function testEntityArrayField()
    {
        $entities = new EntityCollection(IEntity::class);
        $field    = $this->field()->entitiesFrom($entities)->build();

        /** @var ArrayOfEntityIdsType $type */
        $type = $field->getType();
        $this->assertInstanceOf(ArrayOfEntityIdsType::class, $type);
        $this->assertInstanceOf(EntityIdType::class, $type->getElementType());
        $this->assertInstanceOf(EntityIdOptions::class, $type->getElementType()->getOptions());
        $this->assertSame($entities, $type->getElementType()->getOptions()->getEntities());
        $this->assertHasProcessor(new EntityIdArrayValidator(PhpType::arrayOf(PhpType::int())->nullable(), $entities), $field);
        $this->assertHasProcessor(new EntityArrayLoaderProcessor($entities), $field);
        $this->assertEquals(PhpType::arrayOf(PhpType::object(IEntity::class))->nullable(), $field->getProcessedType());
    }

    public function testEntityIdsFromField()
    {
        $entities = new EntityCollection(IEntity::class);
        $field    = $this->field()->entityIdsFrom($entities)->build();

        /** @var ArrayOfEntityIdsType $type */
        $type = $field->getType();
        $this->assertInstanceOf(ArrayOfEntityIdsType::class, $type);
        $this->assertInstanceOf(EntityIdType::class, $type->getElementType());
        $this->assertInstanceOf(EntityIdOptions::class, $type->getElementType()->getOptions());
        $this->assertSame($entities, $type->getElementType()->getOptions()->getEntities());
        $this->assertHasProcessor(new EntityIdArrayValidator(PhpType::arrayOf(PhpType::int())->nullable(), $entities), $field);
        $this->assertEquals(PhpType::arrayOf(PhpType::int())->nullable(), $field->getProcessedType());
    }

    public function testDateField()
    {
        $field = $this->field()->date('Y-m-d', new \DateTimeZone('UTC'))->build();

        /** @var DateType $type */
        $type = $field->getType();
        $this->assertInstanceOf(DateType::class, $type);
        $this->assertSame('Y-m-d', $type->get(DateType::ATTR_FORMAT));
        $this->assertHasProcessor(new DateFormatValidator(PhpType::string()->nullable(), 'Y-m-d'), $field);
        $this->assertHasProcessor(new DateTimeProcessor('Y-m-d', new \DateTimeZone('UTC'), DateTimeProcessor::MODE_ZERO_TIME), $field);
        $this->assertEquals(PhpType::object(\DateTime::class)->nullable(), $field->getProcessedType());
    }

    public function testDateTime()
    {
        $field = $this->field()->datetime('Y-m-d H:i:s', new \DateTimeZone('UTC'))->build();

        /** @var DateTimeType $type */
        $type = $field->getType();
        $this->assertInstanceOf(DateTimeType::class, $type);
        $this->assertSame('Y-m-d H:i:s', $type->get(DateTimeType::ATTR_FORMAT));
        $this->assertHasProcessor(new DateFormatValidator(PhpType::string()->nullable(), 'Y-m-d H:i:s'), $field);
        $this->assertHasProcessor(new DateTimeProcessor('Y-m-d H:i:s', new \DateTimeZone('UTC')), $field);
        $this->assertEquals(PhpType::object(\DateTime::class)->nullable(), $field->getProcessedType());
    }

    public function testTime()
    {
        $field = $this->field()->time('H:i:s', new \DateTimeZone('UTC'))->build();

        /** @var TimeType $type */
        $type = $field->getType();
        $this->assertInstanceOf(TimeType::class, $type);
        $this->assertSame('H:i:s', $type->get(TimeType::ATTR_FORMAT));
        $this->assertHasProcessor(new DateFormatValidator(PhpType::string()->nullable(), 'H:i:s'), $field);
        $this->assertHasProcessor(new DateTimeProcessor('H:i:s', new \DateTimeZone('UTC'), DateTimeProcessor::MODE_ZERO_DATE), $field);
        $this->assertEquals(PhpType::object(\DateTime::class)->nullable(), $field->getProcessedType());
    }

    public function testFileField()
    {
        $field = $this->field()->file()->build();

        /** @var FileType $type */
        $type = $field->getType();
        $this->assertInstanceOf(FileType::class, $type);
        $this->assertEquals(PhpType::object(IUploadedFile::class)->nullable(), $field->getProcessedType());
    }

    public function testImageField()
    {
        $field = $this->field()->image()->build();

        /** @var ImageType $type */
        $type = $field->getType();
        $this->assertInstanceOf(ImageType::class, $type);
        $this->assertEquals(PhpType::object(IUploadedImage::class)->nullable(), $field->getProcessedType());
    }

    public function testInnerForm()
    {
        /** @var IForm $form */
        $form  = $this->getMockForAbstractClass(IForm::class);
        $field = $this->field()->form($form)->build();

        /** @var InnerFormType $type */
        $type = $field->getType();
        $this->assertInstanceOf(InnerFormType::class, $type);
        $this->assertHasProcessor(new TypeValidator(PhpType::arrayOf(PhpType::mixed())->nullable()), $field);
        $this->assertHasProcessor(new InnerFormProcessor($form), $field);
        $this->assertEquals(PhpType::arrayOf(PhpType::mixed())->nullable(), $field->getProcessedType());
    }

    public function testEnum()
    {
        $field = $this->field()->enum(StatusEnum::class, [
                StatusEnum::INACTIVE => 'Inactive',
                StatusEnum::ACTIVE   => 'Active',
        ])->build();

        /** @var StringType $type */
        $type = $field->getType();
        $this->assertInstanceOf(StringType::class, $type);
        $this->assertEquals([
                new FieldOption(StatusEnum::INACTIVE, 'Inactive'),
                new FieldOption(StatusEnum::ACTIVE, 'Active'),
        ], $type->getOptions()->all());
        $this->assertHasProcessor(new OneOfValidator(PhpType::string()->nullable(), [StatusEnum::INACTIVE, StatusEnum::ACTIVE]), $field);
        $this->assertHasProcessor(new EnumProcessor(StatusEnum::class), $field);
        $this->assertEquals(PhpType::object(StatusEnum::class)->nullable(), $field->getProcessedType());
    }

    public function testCustom()
    {
        $field = $this->field()->custom(Type::object(\stdClass::class), [])->build();

        /** @var CustomType $type */
        $type = $field->getType();
        $this->assertInstanceOf(CustomType::class, $type);
        $this->assertEquals((new ObjectType(\stdClass::class))->nullable(), $type->getPhpTypeOfInput());
        $this->assertEquals(null, $type->getOptions());
        $this->assertEquals([new TypeValidator(PhpType::object(\stdClass::class)->nullable())], $field->getProcessors());
        $this->assertEquals(PhpType::object(\stdClass::class)->nullable(), $field->getProcessedType());
    }
}