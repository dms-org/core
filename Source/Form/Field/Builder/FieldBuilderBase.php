<?php

namespace Dms\Core\Form\Field\Builder;

use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Form\Field\Field as ActualField;
use Dms\Core\Form\Field\Options\ArrayFieldOptions;
use Dms\Core\Form\Field\Processor\CustomProcessor;
use Dms\Core\Form\Field\Processor\DefaultValueProcessor;
use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Form\Field\Processor\Validator\CustomValidator;
use Dms\Core\Form\Field\Processor\Validator\OneOfValidator;
use Dms\Core\Form\Field\Processor\Validator\RequiredValidator;
use Dms\Core\Form\Field\Processor\Validator\UniquePropertyValidator;
use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Form\IFieldType;
use Dms\Core\Model\IObjectSet;
use Dms\Core\Model\Type\IType;

/**
 * The base field builder class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class FieldBuilderBase
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var IFieldType|null
     */
    protected $type;

    /**
     * @var IFieldProcessor[]
     */
    protected $processors = [];

    /**
     * @var mixed
     */
    protected $initialValue = null;

    final protected function __construct(FieldBuilderBase $previous = null)
    {
        if ($previous) {
            $this->name         = $previous->name;
            $this->label        = $previous->label;
            $this->type         = $previous->type;
            $this->processors   = $previous->processors;
            $this->initialValue = $previous->initialValue;
        }
    }

    /**
     * Builds the field.
     *
     * @return ActualField
     */
    public function build()
    {
        return new ActualField(
                $this->name,
                $this->label,
                $this->type,
                $this->processors,
                $this->initialValue
        );
    }

    /**
     * @param FieldValidator $validator
     *
     * @return static
     */
    public function validate(FieldValidator $validator)
    {
        $this->processors[] = $validator;

        return $this;
    }

    /**
     * @param IFieldProcessor $processor
     *
     * @return static
     */
    public function process(IFieldProcessor $processor)
    {
        $this->processors[] = $processor;

        return $this;
    }

    /**
     * Sets the type of the field.
     *
     * @param IFieldType $type
     *
     * @return static
     */
    public function type(IFieldType $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Sets the type attribute.
     *
     * @param string $typeAttribute
     * @param mixed  $value
     *
     * @return static
     * @throws InvalidOperationException
     */
    public function attr($typeAttribute, $value)
    {
        return $this->attrs([$typeAttribute => $value]);
    }

    /**
     * Sets the type attributes.
     *
     * @param array $typeAttributes
     *
     * @return static
     * @throws InvalidOperationException
     */
    public function attrs(array $typeAttributes)
    {
        if (!$this->type) {
            throw InvalidOperationException::methodCall(__METHOD__, 'type property type must be set');
        }

        $this->type = $this->type->withAll($typeAttributes);

        return $this;
    }

    /**
     * Sets the initial value of the field.
     *
     * The supplied value is the *processed* initial value and hence
     * must be of the processed type.
     *
     * @param mixed $value
     *
     * @return static
     */
    public function value($value)
    {
        $this->initialValue = $value;

        return $this;
    }

    /**
     * Validates the field is required.
     *
     * @return static
     */
    public function required()
    {
        return $this
                ->validate(new RequiredValidator($this->getCurrentProcessedType(__FUNCTION__)))
                ->attr(FieldType::ATTR_REQUIRED, true);
    }

    /**
     * Sets the default value for the field.
     *
     * @param mixed $value
     *
     * @return static
     */
    public function defaultTo($value)
    {
        return $this
                ->process(new DefaultValueProcessor($this->getCurrentProcessedType(__FUNCTION__), $this->processDefaultValue($value)))
                ->attr(FieldType::ATTR_DEFAULT, $value);
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected function processDefaultValue($value)
    {
        return $value;
    }

    /**
     * Validates the input to be a unique within the object properties.
     *
     * @param IObjectSet $objects
     * @param string     $propertyName
     *
     * @return static
     */
    public function uniqueIn(IObjectSet $objects, $propertyName)
    {
        return $this
                ->validate(new UniquePropertyValidator($this->getCurrentProcessedType(__FUNCTION__), $objects, $propertyName));
    }

    /**
     * Validates the input is one of the supplied values with the option
     * values as the array keys and the labels as the array values.
     *
     * @param array $valueLabelMap
     *
     * @return static
     */
    public function oneOf(array $valueLabelMap)
    {
        return $this
                ->process(new OneOfValidator($this->getCurrentProcessedType(__FUNCTION__), array_keys($valueLabelMap)))
                ->attr(FieldType::ATTR_OPTIONS, ArrayFieldOptions::fromAssocArray($valueLabelMap));
    }

    /**
     * Validates the input according to the supplied callback.
     *
     * Example with message id:
     * <code>
     * ->assert(function ($input) {
     *      return strlen($input) < 2;
     * }, 'some.message-id')
     * </code>
     *
     * Example with custom message ids:
     * <code>
     * ->assert(function ($input, array &$messages) {
     *      if (strlen($input) >= 2) {
     *          $messages[] = 'some.message-id';
     *      }
     * })
     * </code>
     *
     * @param callable    $validation
     * @param string|null $messageId
     * @param array       $parameters
     *
     * @return static
     */
    public function assert(callable $validation, $messageId = null, array $parameters = [])
    {
        return $this->validate(new CustomValidator($this->getCurrentProcessedType(__FUNCTION__), $validation, $messageId, $parameters));
    }

    /**
     * Maps the inputted value according to the supplied callback.
     *
     * Example:
     * <code>
     * ->map(function ($input) {
     *      return $input . '-abc';
     * })
     * </code>
     *
     * @param callable $mapper
     * @param callable $reverseMapper
     * @param IType    $processedType
     *
     * @return static
     */
    public function map(callable $mapper, callable $reverseMapper, IType $processedType)
    {
        return $this->process(new CustomProcessor($processedType, $mapper, $reverseMapper));
    }

    /**
     * @param string $function
     *
     * @return IType
     * @throws InvalidOperationException
     */
    protected function getCurrentProcessedType($function = __FUNCTION__)
    {
        /** @var IFieldProcessor|null $processor */
        $processor = end($this->processors);

        if ($processor) {
            return $processor->getProcessedType();
        } elseif ($this->type) {
            return $this->type->getProcessedPhpType();
        } else {
            throw InvalidOperationException::format(
                    'Invalid call to method \'%s\': field type has not been set on field \'%s\'',
                    $function, $this->name
            );
        }
    }
}