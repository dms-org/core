<?php

namespace Dms\Core\Tests\Form\Field;

use Dms\Core\File\IUploadedFile;
use Dms\Core\File\IUploadedImage;
use Dms\Core\Form\Field\Builder\Field as Field;
use Dms\Core\Form\Field\Options\ArrayFieldOptions;
use Dms\Core\Form\Field\Options\EntityIdOptions;
use Dms\Core\Form\Field\Options\FieldOption;
use Dms\Core\Form\Field\Processor\ArrayAllProcessor;
use Dms\Core\Form\Field\Processor\BoolProcessor;
use Dms\Core\Form\Field\Processor\DateTimeProcessor;
use Dms\Core\Form\Field\Processor\DefaultValueProcessor;
use Dms\Core\Form\Field\Processor\EntityArrayLoaderProcessor;
use Dms\Core\Form\Field\Processor\EntityLoaderProcessor;
use Dms\Core\Form\Field\Processor\EnumProcessor;
use Dms\Core\Form\Field\Processor\InnerFormProcessor;
use Dms\Core\Form\Field\Processor\TypeProcessor;
use Dms\Core\Form\Field\Processor\Validator\BoolValidator;
use Dms\Core\Form\Field\Processor\Validator\DateFormatValidator;
use Dms\Core\Form\Field\Processor\Validator\EntityIdArrayValidator;
use Dms\Core\Form\Field\Processor\Validator\EntityIdValidator;
use Dms\Core\Form\Field\Processor\Validator\OneOfValidator;
use Dms\Core\Form\Field\Processor\Validator\RequiredValidator;
use Dms\Core\Form\Field\Processor\Validator\TypeValidator;
use Dms\Core\Form\Field\Type\ArrayOfEntityIdsType;
use Dms\Core\Form\Field\Type\ArrayOfType;
use Dms\Core\Form\Field\Type\CustomType;
use Dms\Core\Form\Field\Type\DateTimeType;
use Dms\Core\Form\Field\Type\DateType;
use Dms\Core\Form\Field\Type\EntityIdType;
use Dms\Core\Form\Field\Type\EnumType;
use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Form\Field\Type\FileType;
use Dms\Core\Form\Field\Type\ImageType;
use Dms\Core\Form\Field\Type\InnerFormType;
use Dms\Core\Form\Field\Type\ScalarType;
use Dms\Core\Form\Field\Type\StringType;
use Dms\Core\Form\Field\Type\TimeOfDayType;
use Dms\Core\Form\IForm;
use Dms\Core\Model\EntityCollection;
use Dms\Core\Model\EntityIdCollection;
use Dms\Core\Model\IEntity;
use Dms\Core\Model\Object\Entity;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\Builder\Type as PhpType;
use Dms\Core\Model\Type\IType;
use Dms\Core\Model\Type\ObjectType;
use Dms\Core\Tests\Form\Field\Processor\Fixtures\StatusEnum;

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
        $this->assertSame('abcdef', $field->getUnprocessedInitialValue());
    }

    public function testUnprocessedInitialValue()
    {
        $field = $this->field()->value(new \DateTimeImmutable('2000-01-01'))->date('Y-m-d')->build();

        $this->assertEquals(new \DateTimeImmutable('2000-01-01'), $field->getInitialValue());
        $this->assertSame('2000-01-01', $field->getUnprocessedInitialValue());
    }

    public function testRequiredField()
    {
        $field = $this->field()->string()->required()->build();

        $this->assertAttributes([FieldType::ATTR_REQUIRED => true, ScalarType::ATTR_TYPE => IType::STRING], $field);
        $this->assertHasProcessor(new RequiredValidator(PhpType::mixed()), $field);
        $this->assertEquals(PhpType::string(), $field->getProcessedType());
    }

    public function testDefaultValueField()
    {
        $field = $this->field()->string()->defaultTo('abc')->build();

        $this->assertAttributes([FieldType::ATTR_DEFAULT => 'abc', ScalarType::ATTR_TYPE => IType::STRING], $field);
        $this->assertHasProcessor(new DefaultValueProcessor(PhpType::string()->nullable(), 'abc'), $field);
        $this->assertEquals(PhpType::string(), $field->getProcessedType());
    }

    public function testOneOfOptionsField()
    {
        $field = $this->field()->string()->oneOf(['hi' => 'Hi', 'bye' => 'Bye'])->build();

        $this->assertAttributes(
            [
                ScalarType::ATTR_TYPE   => IType::STRING,
                FieldType::ATTR_OPTIONS => $fieldOptions = ArrayFieldOptions::fromAssocArray(['hi' => 'Hi', 'bye' => 'Bye']),
            ],
            $field
        );
        $this->assertHasProcessor(new OneOfValidator(PhpType::string()->nullable(), $fieldOptions), $field);
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
        $this->assertEquals(PhpType::bool(), $field->getProcessedType());
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
        $this->assertHasProcessor(new ArrayAllProcessor([
            new BoolValidator(PhpType::mixed()),
            new BoolProcessor(),
            new DefaultValueProcessor(PhpType::bool(), false),
        ]), $field);
        $this->assertEquals(PhpType::arrayOf(PhpType::bool())->nullable(), $field->getProcessedType());
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

    public function testEntityLabelledByCallback()
    {
        $entity = $this->getMock(IEntity::class);
        $entity->method('getId')->willReturn(5);

        $entities = new EntityCollection(IEntity::class, [$entity]);
        $field    = $this->field()->entityIdFrom($entities)
            ->labelledByCallback(function (IEntity $entity) {
                return 'ID: ' . $entity->getId();
            })
            ->build();

        /** @var EntityIdType $type */
        $type = $field->getType();

        $this->assertEquals([new FieldOption(5, 'ID: 5')], $type->getOptions()->getAll());
    }

    public function testEntityLabelledByMemberExpression()
    {
        $entity = $this->getMock(Entity::class);
        $entity->setId(5);

        $entities = new EntityCollection(Entity::class, [$entity]);
        $field    = $this->field()->entityFrom($entities)
            ->labelledBy(Entity::ID)
            ->build();

        /** @var ArrayOfType $type */
        $type = $field->getType();

        $this->assertEquals([new FieldOption(5, '5')], $type->getOptions()->getAll());
    }


    public function testEntityArrayField()
    {
        $entities = new EntityCollection(IEntity::class);
        $field    = $this->field()->entitiesFrom($entities)->build();

        /** @var ArrayOfEntityIdsType $type */
        $type = $field->getType();
        $this->assertInstanceOf(ArrayOfEntityIdsType::class, $type);
        $this->assertSame(true, $type->get(ArrayOfEntityIdsType::ATTR_UNIQUE_ELEMENTS));

        $this->assertInstanceOf(EntityIdType::class, $type->getElementType());
        $this->assertInstanceOf(EntityIdOptions::class, $type->getElementType()->getOptions());
        $this->assertSame($entities, $type->getElementType()->getOptions()->getEntities());
        $this->assertHasProcessor(new EntityIdArrayValidator(PhpType::arrayOf(PhpType::int())->nullable(), $entities), $field);
        $this->assertHasProcessor(new EntityArrayLoaderProcessor($entities), $field);
        $this->assertEquals(PhpType::arrayOf(PhpType::object(IEntity::class))->nullable(), $field->getProcessedType());
    }

    public function testEntityArrayLabelledByMemberExpression()
    {
        $entity = $this->getMock(Entity::class);
        $entity->setId(5);

        $entities = new EntityCollection(Entity::class, [$entity]);
        $field    = $this->field()->entityIdsFrom($entities)
            ->labelledBy(Entity::ID)
            ->build();

        /** @var ArrayOfEntityIdsType $type */
        $type = $field->getType();

        $this->assertEquals([new FieldOption(5, '5')], $type->getElementType()->getOptions()->getAll());
    }

    public function testEntityArrayLabelledByCallback()
    {
        $entity = $this->getMock(IEntity::class);
        $entity->method('getId')->willReturn(5);

        $entities = new EntityCollection(IEntity::class, [$entity]);
        $field    = $this->field()->entityIdsFrom($entities)
            ->labelledByCallback(function (IEntity $entity) {
                return 'ID: ' . $entity->getId();
            })
            ->build();

        /** @var ArrayOfEntityIdsType $type */
        $type = $field->getType();

        $this->assertEquals([new FieldOption(5, 'ID: 5')], $type->getElementType()->getOptions()->getAll());
    }

    public function testEntityFieldMappedToObjectCollection()
    {
        $entity = $this->getMock(Entity::class);
        $entity->hydrate(['id' => 1]);

        $entities = new EntityCollection(Entity::class, [$entity]);
        $field    = $this->field()
            ->entitiesFrom($entities)
            ->mapToCollection(Entity::collectionType())
            ->build();

        /** @var ArrayOfEntityIdsType $type */
        $this->assertEquals(Entity::collectionType()->nullable(), $field->getProcessedType());

        $this->assertEquals(Entity::collection([$entity]), $field->process(['1']));
        $this->assertEquals([1], $field->unprocess(Entity::collection([$entity])));
    }

    public function testEntityIdsFromField()
    {
        $entities = new EntityCollection(IEntity::class);
        $field    = $this->field()->entityIdsFrom($entities)->build();

        /** @var ArrayOfEntityIdsType $type */
        $type = $field->getType();
        $this->assertInstanceOf(ArrayOfEntityIdsType::class, $type);
        $this->assertSame(true, $type->get(ArrayOfEntityIdsType::ATTR_UNIQUE_ELEMENTS));

        $this->assertInstanceOf(EntityIdType::class, $type->getElementType());
        $this->assertInstanceOf(EntityIdOptions::class, $type->getElementType()->getOptions());
        $this->assertSame($entities, $type->getElementType()->getOptions()->getEntities());
        $this->assertHasProcessor(new EntityIdArrayValidator(PhpType::arrayOf(PhpType::int())->nullable(), $entities), $field);
        $this->assertEquals(PhpType::arrayOf(PhpType::int())->nullable(), $field->getProcessedType());
    }

    public function testEntityIdFieldMappedToCollection()
    {
        $entity = $this->getMock(IEntity::class);
        $entity->method('getId')->willReturn(1);

        $entities = new EntityCollection(IEntity::class, [$entity]);
        $field    = $this->field()->entityIdsFrom($entities)->mapToCollection(EntityIdCollection::type())->build();

        /** @var ArrayOfEntityIdsType $type */
        $this->assertEquals(PhpType::collectionOf(PhpType::int(), EntityIdCollection::class)->nullable(), $field->getProcessedType());

        $this->assertEquals(new EntityIdCollection([1]), $field->process(['1']));
        $this->assertEquals([1], $field->unprocess(new EntityIdCollection([1])));
    }

    public function testEntityIdFieldMappedToCollectionWithRequired()
    {
        $entities = new EntityCollection(IEntity::class, []);
        $field    = $this->field()->entityIdsFrom($entities)
            ->mapToCollection(EntityIdCollection::type())
            ->required()
            ->build();

        /** @var ArrayOfEntityIdsType $type */
        $this->assertEquals(PhpType::collectionOf(PhpType::int(), EntityIdCollection::class), $field->getProcessedType());
    }

    public function testEntityIdsFieldMappedToObjectCollection()
    {
        $entity = $this->getMock(Entity::class);
        $entity->hydrate(['id' => 1]);

        $entities = new EntityCollection(Entity::class, [$entity]);
        $field    = $this->field()->entityIdsFrom($entities)->mapToCollection(
            Entity::collectionType(),
            function (int $id) use ($entities) {
                return $entities->get($id);
            },
            function (Entity $entity) {
                return $entity->getId();
            }
        )->build();

        /** @var ArrayOfEntityIdsType $type */
        $this->assertEquals(Entity::collectionType()->nullable(), $field->getProcessedType());

        $this->assertEquals(Entity::collection([$entity]), $field->process(['1']));
        $this->assertEquals([1], $field->unprocess(Entity::collection([$entity])));
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
        $this->assertEquals(PhpType::object(\DateTimeImmutable::class)->nullable(), $field->getProcessedType());
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
        $this->assertEquals(PhpType::object(\DateTimeImmutable::class)->nullable(), $field->getProcessedType());
    }

    public function testTime()
    {
        $field = $this->field()->time('H:i:s', new \DateTimeZone('UTC'))->build();

        /** @var TimeOfDayType $type */
        $type = $field->getType();
        $this->assertInstanceOf(TimeOfDayType::class, $type);
        $this->assertSame('H:i:s', $type->get(TimeOfDayType::ATTR_FORMAT));
        $this->assertHasProcessor(new DateFormatValidator(PhpType::string()->nullable(), 'H:i:s'), $field);
        $this->assertHasProcessor(new DateTimeProcessor('H:i:s', new \DateTimeZone('UTC'), DateTimeProcessor::MODE_ZERO_DATE), $field);
        $this->assertEquals(PhpType::object(\DateTimeImmutable::class)->nullable(), $field->getProcessedType());
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
        $this->assertInstanceOf(EnumType::class, $type);
        $this->assertEquals($fieldOptions = [
            new FieldOption(StatusEnum::INACTIVE, 'Inactive'),
            new FieldOption(StatusEnum::ACTIVE, 'Active'),
        ], $type->getOptions()->getAll());

        $this->assertHasProcessor(new OneOfValidator(PhpType::string()->nullable(), new ArrayFieldOptions($fieldOptions)), $field);
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