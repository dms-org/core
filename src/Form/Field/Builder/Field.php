<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Builder;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Field\Type\ArrayOfEntityIdsType;
use Dms\Core\Form\Field\Type\ArrayOfType;
use Dms\Core\Form\Field\Type\BoolType;
use Dms\Core\Form\Field\Type\CustomType;
use Dms\Core\Form\Field\Type\DateTimeType;
use Dms\Core\Form\Field\Type\DateTimeTypeBase;
use Dms\Core\Form\Field\Type\DateType;
use Dms\Core\Form\Field\Type\EntityIdType;
use Dms\Core\Form\Field\Type\EnumType;
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
     * @return EntityFieldBuilder
     */
    public function entityFrom(IEntitySet $entities) : EntityFieldBuilder
    {
        return new EntityFieldBuilder(
                $this->type(new EntityIdType($entities, $loadAsObjects = true))
        );
    }

    /**
     * Validates the input as a entity id.
     *
     * @param IEntitySet $entities
     *
     * @return EntityFieldBuilder
     */
    public function entityIdFrom(IEntitySet $entities) : EntityFieldBuilder
    {
        return new EntityFieldBuilder($this->type(new EntityIdType($entities)));
    }

    /**
     * Validates the input as an array of entity ids.
     *
     * @param IEntitySet $entities
     *
     * @return EntityArrayFieldBuilder
     */
    public function entityIdsFrom(IEntitySet $entities) : EntityArrayFieldBuilder
    {
        return (new EntityArrayFieldBuilder($this
                ->type(new ArrayOfEntityIdsType(
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
     * @return EntityArrayFieldBuilder
     */
    public function entitiesFrom(IEntitySet $entities) : EntityArrayFieldBuilder
    {
        return (new EntityArrayFieldBuilder($this
                ->type(new ArrayOfEntityIdsType(
                        $entities,
                        Field::element()->entityIdFrom($entities)->required()->build(),
                        $loadAsObjects = true
                ))
        ))->containsNoDuplicates();
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