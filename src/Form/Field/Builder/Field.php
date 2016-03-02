<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Builder;

use Dms\Core\Common\Crud\IReadModule;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Field\Options\EntityIdOptions;
use Dms\Core\Form\Field\Options\ObjectIndexOptions;
use Dms\Core\Form\Field\Type\ArrayOfObjectIdsType;
use Dms\Core\Form\Field\Type\ArrayOfType;
use Dms\Core\Form\Field\Type\BoolType;
use Dms\Core\Form\Field\Type\CustomType;
use Dms\Core\Form\Field\Type\DateTimeType;
use Dms\Core\Form\Field\Type\DateTimeTypeBase;
use Dms\Core\Form\Field\Type\DateType;
use Dms\Core\Form\Field\Type\EnumType;
use Dms\Core\Form\Field\Type\FileType;
use Dms\Core\Form\Field\Type\FloatType;
use Dms\Core\Form\Field\Type\ImageType;
use Dms\Core\Form\Field\Type\InnerCrudModuleType;
use Dms\Core\Form\Field\Type\InnerFormType;
use Dms\Core\Form\Field\Type\IntType;
use Dms\Core\Form\Field\Type\ObjectIdType;
use Dms\Core\Form\Field\Type\StringType;
use Dms\Core\Form\Field\Type\TimeOfDayType;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Form\IForm;
use Dms\Core\Form\Object\FormObject;
use Dms\Core\Form\Object\InnerFormObjectFieldBuilder;
use Dms\Core\Form\Object\Type\InnerFormObjectType;
use Dms\Core\Model\IEntitySet;
use Dms\Core\Model\IObjectSetWithIdentityByIndex;
use Dms\Core\Model\Type\IType;

/**
 * The field builder class.
 * This is the entry point to the field builder classes.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Field extends FieldBuilderBase
{
    /**
     * @return FieldNameBuilder
     */
    final public static function create() : FieldNameBuilder
    {
        return new FieldNameBuilder();
    }

    /**
     * @param string $fieldName
     *
     * @return FieldLabelBuilder
     */
    final public static function name(string $fieldName) : FieldLabelBuilder
    {
        $labelBuilder       = new FieldLabelBuilder();
        $labelBuilder->name = $fieldName;

        return $labelBuilder;
    }

    /**
     * Constructs a field builder to be used as an element of
     * an array field.
     *
     * @return Field
     */
    final public static function element() : Field
    {
        $labelBuilder        = new self();
        $labelBuilder->name  = '__element';
        $labelBuilder->label = '__element';

        return $labelBuilder;
    }

    /**
     * Constructs a field builder to be used as placeholder for a field type.
     *
     * @return Field
     */
    final public static function forType() : Field
    {
        $labelBuilder        = new self();
        $labelBuilder->name  = '__type';
        $labelBuilder->label = '__type';

        return $labelBuilder;
    }

    /**
     * Validates the input as a string.
     *
     * @return StringFieldBuilder
     */
    public function string() : StringFieldBuilder
    {
        return new StringFieldBuilder($this->type(new StringType()));
    }

    /**
     * Validates the input as a integer.
     *
     * @return IntFieldBuilder
     */
    public function int() : IntFieldBuilder
    {
        return new IntFieldBuilder($this->type(new IntType()));
    }

    /**
     * Validates the input as a decimal (float).
     *
     * @return DecimalFieldBuilder
     */
    public function decimal() : DecimalFieldBuilder
    {
        return new DecimalFieldBuilder($this->type(new FloatType()));
    }

    /**
     * Validates the input as a boolean.
     *
     * @return BoolFieldBuilder
     */
    public function bool() : BoolFieldBuilder
    {
        return new BoolFieldBuilder($this->type(new BoolType()));
    }

    /**
     * Validates the input with the supplied format and
     * converts the input to a DateTimeImmutable instance.
     *
     * @see \DateTimeImmutable
     *
     * @param string        $format
     * @param \DateTimeZone $timeZone
     *
     * @return DateFieldBuilder
     */
    public function date(string $format, \DateTimeZone $timeZone = null) : DateFieldBuilder
    {
        return $this->dateTypeBuilder(new DateType($format, $timeZone));
    }

    /**
     * Validates the input with the supplied format and
     * converts the input to a DateTimeImmutable instance.
     *
     * @see \DateTimeImmutable
     *
     * @param string        $format
     * @param \DateTimeZone $timeZone
     *
     * @return DateFieldBuilder
     */
    public function datetime(string $format, \DateTimeZone $timeZone = null) : DateFieldBuilder
    {
        return $this->dateTypeBuilder(new DateTimeType($format, $timeZone));
    }

    /**
     * Validates the input with the supplied format and
     * converts the input to a DateTimeImmutable instance.
     *
     * @see \DateTimeImmutable
     *
     * @param string        $format
     * @param \DateTimeZone $timeZone
     *
     * @return DateFieldBuilder
     */
    public function time(string $format, \DateTimeZone $timeZone = null) : DateFieldBuilder
    {
        return $this->dateTypeBuilder(new TimeOfDayType($format, $timeZone));
    }

    /**
     * @param DateTimeTypeBase $type
     *
     * @return DateFieldBuilder
     */
    private function dateTypeBuilder(DateTimeTypeBase $type) : DateFieldBuilder
    {
        $this->type($type);

        return new DateFieldBuilder($type, $this);
    }

    /**
     * Validates the input as an uploaded file.
     *
     * @return FileFieldBuilder
     */
    public function file() : FileFieldBuilder
    {
        return new FileFieldBuilder($this->type(new FileType()));
    }

    /**
     * Validates the input as an uploaded image.
     *
     * @return ImageFieldBuilder
     */
    public function image() : ImageFieldBuilder
    {
        return new ImageFieldBuilder($this->type(new ImageType()));
    }

    /**
     * Validates the input as a entity id that will load
     * the entity object instance.
     *
     * @param IEntitySet $entities
     *
     * @return ObjectFieldBuilder
     */
    public function entityFrom(IEntitySet $entities) : ObjectFieldBuilder
    {
        return new ObjectFieldBuilder(
            $this->type(new ObjectIdType(new EntityIdOptions($entities), $loadAsObjects = true))
        );
    }

    /**
     * Validates the input as a entity id.
     *
     * @param IEntitySet $entities
     *
     * @return ObjectFieldBuilder
     */
    public function entityIdFrom(IEntitySet $entities) : ObjectFieldBuilder
    {
        return new ObjectFieldBuilder($this->type(new ObjectIdType(new EntityIdOptions($entities))));
    }

    /**
     * Validates the input as an array of entity ids.
     *
     * @param IEntitySet $entities
     *
     * @return ObjectArrayFieldBuilder
     */
    public function entityIdsFrom(IEntitySet $entities) : ObjectArrayFieldBuilder
    {
        return (new ObjectArrayFieldBuilder($this
            ->type(new ArrayOfObjectIdsType(
                $entities,
                Field::element()->entityIdFrom($entities)->required()->build()
            ))
        ))->containsNoDuplicates();
    }

    /**
     * Validates the input as an array of entity ids
     * and will load the array of entities from the set.
     *
     * @param IEntitySet $entities
     *
     * @return ObjectArrayFieldBuilder
     */
    public function entitiesFrom(IEntitySet $entities) : ObjectArrayFieldBuilder
    {
        return (new ObjectArrayFieldBuilder($this
            ->type(new ArrayOfObjectIdsType(
                $entities,
                Field::element()->entityIdFrom($entities)->required()->build(),
                $loadAsObjects = true
            ))
        ))->containsNoDuplicates();
    }

    /**
     * Validates the input as the index of an object and will load
     * the object instance at the index.
     *
     * @param IObjectSetWithIdentityByIndex $objects
     *
     * @return ObjectFieldBuilder
     */
    public function objectFromIndex(IObjectSetWithIdentityByIndex $objects) : ObjectFieldBuilder
    {
        return new ObjectFieldBuilder(
            $this->type(new ObjectIdType(new ObjectIndexOptions($objects), $loadAsObjects = true))
        );
    }

    /**
     * Validates the input as an array of indexes from the supplied object set
     * and will load the object instances.
     *
     * @param IObjectSetWithIdentityByIndex $objects
     *
     * @return ObjectArrayFieldBuilder
     */
    public function objectsFromIndexes(IObjectSetWithIdentityByIndex $objects) : ObjectArrayFieldBuilder
    {
        return (new ObjectArrayFieldBuilder($this
            ->type(new ArrayOfObjectIdsType(
                $objects,
                Field::element()->objectFromIndex($objects)->required()->build(),
                $loadAsObjects = true
            ))
        ));
    }

    /**
     * Validates the input as an array of elements.
     *
     * @param FieldBuilderBase $elementField
     *
     * @return ArrayOfFieldBuilder
     */
    public function arrayOf(FieldBuilderBase $elementField) : ArrayOfFieldBuilder
    {
        return $this->arrayOfField($elementField->build());
    }

    /**
     * Validates the input as an array of elements.
     *
     * @param IField $elementField
     *
     * @return ArrayOfFieldBuilder
     */
    public function arrayOfField(IField $elementField) : ArrayOfFieldBuilder
    {
        return new ArrayOfFieldBuilder($this->type(new ArrayOfType($elementField)));
    }

    /**
     * Validates the input as an submission to a inner form.
     *
     * @param IForm $form
     *
     * @return InnerFormFieldBuilder
     */
    public function form(IForm $form) : InnerFormFieldBuilder
    {
        if ($form instanceof FormObject) {
            return new InnerFormObjectFieldBuilder($this->type(new InnerFormObjectType($form)));
        }

        return new InnerFormFieldBuilder($this->type(new InnerFormType($form)));
    }

    /**
     * Validates the input as one of the enum options maps the
     * value to an instance of the supplied enum class.
     *
     * @param string   $enumClass
     * @param string[] $valueLabelMap
     *
     * @return FieldBuilderBase
     * @throws InvalidArgumentException
     */
    public function enum(string $enumClass, array $valueLabelMap) : FieldBuilderBase
    {
        return $this->type(new EnumType($enumClass, $valueLabelMap));
    }

    /**
     * Defines an inner crud module field.
     *
     * @param IReadModule $module
     *
     * @return FieldBuilderBase
     */
    public function module(IReadModule $module) : FieldBuilderBase
    {
        $this->type(new InnerCrudModuleType($module))->value($module->getDataSource());

        return $this;
    }

    /**
     * Sets the form as a custom object type.
     *
     * @param IType             $type
     * @param IFieldProcessor[] $processors
     *
     * @return FieldBuilderBase
     */
    public function custom(IType $type, array $processors) : FieldBuilderBase
    {
        $this->type(new CustomType($type, $processors));

        return $this;
    }
}