<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Builder;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Form\Field\Field as ActualField;
use Dms\Core\Form\Field\Options\ArrayFieldOptions;
use Dms\Core\Form\Field\Options\CallbackFieldOptions;
use Dms\Core\Form\Field\Options\FieldOption;
use Dms\Core\Form\Field\Processor\CustomProcessor;
use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Form\Field\Processor\TypeProcessor;
use Dms\Core\Form\Field\Processor\Validator\CustomValidator;
use Dms\Core\Form\Field\Processor\Validator\UniquePropertyValidator;
use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Form\IFieldOption;
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
     * @var mixed
     */
    protected $initialValue;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var callable[]
     */
    protected $customProcessorCallbacks = [];

    public function __construct(FieldBuilderBase $previous = null)
    {
        if ($previous) {
            $this->name                     = $previous->name;
            $this->label                    = $previous->label;
            $this->attributes               = $previous->attributes;
            $this->type                     = $previous->type;
            $this->initialValue             = $previous->initialValue;
            $this->customProcessorCallbacks = $previous->customProcessorCallbacks;
        }
    }

    /**
     * Builds the field.
     *
     * @return ActualField
     */
    public function build() : ActualField
    {
        $type                 = $this->type->withAll($this->attributes);
        $currentProcessedType = $type->getProcessedPhpType();
        $customerProcessors   = [];

        foreach ($this->customProcessorCallbacks as $callback) {
            /** @var IFieldProcessor $processor */
            $processor = $callback($currentProcessedType, $type, $this->initialValue);

            $customerProcessors[] = $processor;
            $currentProcessedType = $processor->getProcessedType();
        }

        return new ActualField(
            $this->name,
            $this->label,
            $type,
            $customerProcessors,
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
        $this->customProcessorCallbacks[] = function () use ($validator) {
            return $validator;
        };

        return $this;
    }

    /**
     * @param IFieldProcessor $processor
     *
     * @return static
     * @throws InvalidArgumentException
     */
    public function process(IFieldProcessor $processor)
    {
        $this->customProcessorCallbacks[] = function () use ($processor) {
            return $processor;
        };

        if ($this->initialValue !== null) {
            $messages           = [];
            $this->initialValue = $processor->process($this->initialValue, $messages);

            if (!empty($messages)) {
                throw InvalidArgumentException::format(
                    'Invalid initial value passed to processor for field %s for processor of type %s',
                    $this->name, get_class($processor)
                );
            }
        }

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
    public function attr(string $typeAttribute, $value)
    {
        $this->attributes[$typeAttribute] = $value;

        return $this;
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
        $this->attributes = $typeAttributes + $this->attributes;

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
        return $this->attr(FieldType::ATTR_REQUIRED, true);
    }

    /**
     * Sets the field a readonly.
     *
     * @return static
     */
    public function readonly()
    {
        return $this->attr(FieldType::ATTR_READ_ONLY, true);
    }

    /**
     * Sets the field as hidden.
     *
     * @return static
     */
    public function hidden()
    {
        return $this->attr(FieldType::ATTR_HIDDEN, true);
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
        return $this->attr(FieldType::ATTR_DEFAULT, $this->processDefaultValue($value));
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
    public function uniqueIn(IObjectSet $objects, string $propertyName)
    {
        $this->customProcessorCallbacks[] = function (IType $currentType, IFieldType $fieldType, $initialValue) use ($objects, $propertyName) {
            return new UniquePropertyValidator(
                $currentType,
                $objects,
                $propertyName,
                $initialValue
            );
        };

        return $this;
    }

    /**
     * Validates the input is one of the supplied values with the option
     * values as the array keys and the labels as the array values.
     * Alternatively an array of {@see IFieldOption} can be passed as the options.
     *
     * @param array|IFieldOption[] $valueLabelMap
     * @param string               $valueType
     *
     * @return static
     */
    public function oneOf(array $valueLabelMap, string $valueType = 'string')
    {
        return $this->attr(FieldType::ATTR_OPTIONS, ArrayFieldOptions::fromAssocArray($valueLabelMap, $valueType));
    }


    /**
     * Validates the input is one of the supplied values loaded from the supplied callback.
     * This is useful for loading options from a external data source.
     *
     * Example:
     * <code>
     * ->oneOfOptionsFromCallback(function (string $filter = null) {
     *      return [
     *          'value'         => 'Label',
     *          'another-value' => 'Another Label',
     *      ];
     * });
     * </code>
     *
     *
     * @param callable      $valueLabelMapCallback
     * @param callable|null $labelFromValueLoader
     *
     * @return static
     */
    public function oneOfOptionsFromCallback(callable $valueLabelMapCallback, callable $labelFromValueLoader = null)
    {
        $valueLabelMapCallback = function (string $filter = null) use ($valueLabelMapCallback) {
            $options = [];

            foreach ($valueLabelMapCallback($filter) as $key => $value) {
                $options[] = $value instanceof IFieldOption ? $value : new FieldOption($key, $value);
            }

            return $options;
        };

        if ($labelFromValueLoader) {
            $labelFromValueLoader = function ($value) use ($labelFromValueLoader) {
                $option = $labelFromValueLoader($value);

                return $option ? new FieldOption($value, $option) : null;
            };
        }

        return $this->attr(FieldType::ATTR_OPTIONS, new CallbackFieldOptions($valueLabelMapCallback, $labelFromValueLoader));
    }

    /**
     * Sets that the field should show all the options on the screen.
     *
     * @return static
     */
    public function showAllOptions()
    {
        return $this->attr(FieldType::ATTR_SHOW_ALL_OPTIONS, true);
    }

    /**
     * Casts the input to the supplied scalar type.
     *
     * Accepts values accepted by `settype`:
     * "bool"
     * "int"
     * "float"
     * "string"
     * "array"
     * "object"
     * "null"
     *
     * @param string $scalarType
     *
     * @return static
     */
    public function castTo(string $scalarType)
    {
        return $this->process(new TypeProcessor($scalarType));
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
    public function assert(callable $validation, string $messageId = null, array $parameters = [])
    {
        $this->customProcessorCallbacks[] = function (IType $currentType) use ($validation, $messageId, $parameters) {
            return new CustomValidator($currentType, $validation, $messageId, $parameters);
        };

        return $this;
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
}