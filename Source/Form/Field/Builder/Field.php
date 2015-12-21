<?php

namespace Dms\Core\Form\Field\Builder;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Field\Processor\EntityArrayLoaderProcessor;
use Dms\Core\Form\Field\Processor\EntityLoaderProcessor;
use Dms\Core\Form\Field\Processor\EnumProcessor;
use Dms\Core\Form\Field\Processor\TypeProcessor;
use Dms\Core\Form\Field\Type\ArrayOfEntityIdsType;
use Dms\Core\Form\Field\Type\ArrayOfType;
use Dms\Core\Form\Field\Type\BoolType;
use Dms\Core\Form\Field\Type\CustomType;
use Dms\Core\Form\Field\Type\DateTimeType;
use Dms\Core\Form\Field\Type\DateTimeTypeBase;
use Dms\Core\Form\Field\Type\DateType;
use Dms\Core\Form\Field\Type\EntityIdType;
use Dms\Core\Form\Field\Type\FileType;
use Dms\Core\Form\Field\Type\FloatType;
use Dms\Core\Form\Field\Type\ImageType;
use Dms\Core\Form\Field\Type\InnerFormType;
use Dms\Core\Form\Field\Type\IntType;
use Dms\Core\Form\Field\Type\StringType;
use Dms\Core\Form\Field\Type\TimeOfDayType;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Form\IForm;
use Dms\Core\Form\Object\FormObject;
use Dms\Core\Form\Object\InnerFormObjectFieldBuilder;
use Dms\Core\Form\Object\Type\InnerFormObjectType;
use Dms\Core\Model\IEntitySet;
use Dms\Core\Model\Object\Enum;
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
    final public static function create()
    {
        return new FieldNameBuilder();
    }

    /**
     * @param string $fieldName
     *
     * @return FieldLabelBuilder
     */
    final public static function name($fieldName)
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
    final public static function element()
    {
        $labelBuilder        = new self();
        $labelBuilder->name  = '__element';
        $labelBuilder->label = '__element';

        return $labelBuilder;
    }

    /**
     * Constructs a field builder to be used as an element of
     * an array field.
     *
     * @return Field
     */
    final public static function forType()
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
    public function string()
    {
        return new StringFieldBuilder($this->type(new StringType()));
    }

    /**
     * Validates the input as a integer.
     *
     * @return IntFieldBuilder
     */
    public function int()
    {
        return new IntFieldBuilder($this->type(new IntType()));
    }

    /**
     * Validates the input as a decimal (float).
     *
     * @return DecimalFieldBuilder
     */
    public function decimal()
    {
        return new DecimalFieldBuilder($this->type(new FloatType()));
    }

    /**
     * Validates the input as a boolean.
     *
     * @return BoolFieldBuilder
     */
    public function bool()
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
    public function date($format, \DateTimeZone $timeZone = null)
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
    public function datetime($format, \DateTimeZone $timeZone = null)
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
    public function time($format, \DateTimeZone $timeZone = null)
    {
        return $this->dateTypeBuilder(new TimeOfDayType($format, $timeZone));
    }

    /**
     * @param DateTimeTypeBase $type
     *
     * @return DateFieldBuilder
     */
    private function dateTypeBuilder(
            DateTimeTypeBase $type
    ) {
        $this->type($type);

        return new DateFieldBuilder($type, $this);
    }

    /**
     * Validates the input as an uploaded file.
     *
     * @return FileFieldBuilder
     */
    public function file()
    {
        return new FileFieldBuilder($this->type(new FileType()));
    }

    /**
     * Validates the input as an uploaded image.
     *
     * @return ImageFieldBuilder
     */
    public function image()
    {
        return new ImageFieldBuilder($this->type(new ImageType()));
    }

    /**
     * Validates the input as a entity id that will load
     * the entity object instance.
     *
     * @param IEntitySet $entities
     *
     * @return EntityFieldBuilder
     */
    public function entityFrom(IEntitySet $entities)
    {
        return new EntityFieldBuilder($this
                ->type(new EntityIdType($entities))
                ->process(new EntityLoaderProcessor($entities))
        );
    }

    /**
     * Validates the input as a entity id.
     *
     * @param IEntitySet $entities
     *
     * @return EntityFieldBuilder
     */
    public function entityIdFrom(IEntitySet $entities)
    {
        return new EntityFieldBuilder($this->type(new EntityIdType($entities)));
    }

    /**
     * Validates the input as an array of entity ids.
     *
     * @param IEntitySet $entities
     *
     * @return ArrayOfFieldBuilder
     */
    public function entityIdsFrom(IEntitySet $entities)
    {
        return new ArrayOfFieldBuilder($this
                ->type(new ArrayOfEntityIdsType($entities, Field::element()->entityIdFrom($entities)->required()->build()))
        );
    }

    /**
     * Validates the input as an array of entity ids
     * and will load the array of entities from the set.
     *
     * @param IEntitySet $entities
     *
     * @return EntityArrayFieldBuilder
     */
    public function entitiesFrom(IEntitySet $entities)
    {
        return new EntityArrayFieldBuilder($this
                ->type(new ArrayOfEntityIdsType($entities, Field::element()->entityIdFrom($entities)->required()->build()))
                ->process(new EntityArrayLoaderProcessor($entities))
        );
    }

    /**
     * Validates the input as an array of elements.
     * The supplied callback will be passed an instance of FieldBuilder
     * to define the array element validation.
     *
     * @param FieldBuilderBase $elementField
     *
     * @return ArrayOfFieldBuilder
     */
    public function arrayOf(FieldBuilderBase $elementField)
    {
        return $this->arrayOfField($elementField->build());
    }

    /**
     * Validates the input as an array of elements.
     * The supplied callback will be passed an instance of FieldBuilder
     * to define the array element validation.
     *
     * @param IField $elementField
     *
     * @return ArrayOfFieldBuilder
     */
    public function arrayOfField(IField $elementField)
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
    public function form(IForm $form)
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
    public function enum($enumClass, array $valueLabelMap)
    {
        if (!is_subclass_of($enumClass, Enum::class, true)) {
            throw InvalidArgumentException::format(
                    'Invalid enum class: expecting instance of %s, %s given',
                    Enum::class, $enumClass
            );
        };

        // TODO: verify complex enum types.
        /** @var Enum|string $enumClass */
        $this->type(new StringType())
                ->process(new TypeProcessor($enumClass::getEnumType()->asTypeString()))
                ->oneOf(array_intersect_key($valueLabelMap, array_flip($enumClass::getOptions())))
                ->process(new EnumProcessor($enumClass));

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
    public function custom(IType $type, array $processors)
    {
        $this->type(new CustomType($type, $processors));

        return $this;
    }
}