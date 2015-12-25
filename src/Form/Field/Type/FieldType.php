<?php

namespace Dms\Core\Form\Field\Type;

use Dms\Core\Form\Field\Options\ArrayFieldOptions;
use Dms\Core\Form\Field\Processor\DefaultValueProcessor;
use Dms\Core\Form\Field\Processor\Validator\NotSuppliedValidator;
use Dms\Core\Form\Field\Processor\Validator\OneOfValidator;
use Dms\Core\Form\Field\Processor\Validator\RequiredValidator;
use Dms\Core\Form\Field\Processor\Validator\TypeValidator;
use Dms\Core\Form\IFieldOptions;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Form\IFieldType;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\IType as IPhpType;
use Dms\Core\Model\Type\MixedType;

/**
 * The field type base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class FieldType implements IFieldType
{
    const ATTR_REQUIRED = 'required';
    const ATTR_READ_ONLY = 'read-only';
    const ATTR_DEFAULT = 'default';
    const ATTR_INITIAL_VALUE = 'initial-value';
    const ATTR_OPTIONS = 'options';

    /**
     * @var array
     */
    protected $attributes = [
            self::ATTR_REQUIRED      => null,
            self::ATTR_READ_ONLY     => null,
            self::ATTR_DEFAULT       => null,
            self::ATTR_INITIAL_VALUE => null,
            self::ATTR_OPTIONS       => null,
    ];

    /**
     * @var IPhpType
     */
    protected $inputType;

    /**
     * @var IFieldProcessor[]
     */
    protected $processors;

    /**
     * @var IPhpType
     */
    protected $processedType;

    /**
     * FieldType constructor.
     */
    public function __construct()
    {
        $this->initializeFromCurrentAttributes();
    }

    /**
     * @return bool
     */
    protected function hasTypeSpecificRequiredValidator()
    {
        return false;
    }


    /**
     * @return bool
     */
    protected function hasTypeSpecificOptionsValidator()
    {
        return false;
    }

    /**
     * @return void
     */
    protected function initializeFromCurrentAttributes()
    {
        $this->inputType = $this->buildPhpTypeOfInput()->nullable();

        $processors = [];

        if (!($this->inputType instanceof MixedType)) {
            $processors[] = new TypeValidator($this->inputType);
        }

        if ($this->get(self::ATTR_REQUIRED) && !$this->hasTypeSpecificRequiredValidator()) {
            $processors[] = new RequiredValidator($this->inputType);
        }

        $processors = array_merge($processors, $this->buildProcessors());

        /** @var IFieldProcessor|false $lastProcessor */
        $lastProcessor        = end($processors);
        $currentProcessedType = $lastProcessor ? $lastProcessor->getProcessedType() : $this->inputType;

        $options = $this->get(self::ATTR_OPTIONS);
        if ($options instanceof ArrayFieldOptions && !$this->hasTypeSpecificOptionsValidator()) {
            $processors[] = new OneOfValidator($currentProcessedType, $options);
        }

        if ($this->has(self::ATTR_DEFAULT)) {
            $processors[] = new DefaultValueProcessor(
                    $currentProcessedType,
                    $this->get(self::ATTR_DEFAULT)
            );
        }

        $this->processors = $processors;

        /** @var IFieldProcessor|false $lastProcessor */
        $lastProcessor = end($processors);

        $this->processedType = $lastProcessor ? $lastProcessor->getProcessedType() : $this->inputType;

        if ($this->get(self::ATTR_REQUIRED)) {
            $this->processedType = $this->processedType->nonNullable();
        }

        if ($this->get(self::ATTR_READ_ONLY)) {
            $this->processors = [
                new NotSuppliedValidator(Type::mixed()),
                new DefaultValueProcessor($this->processedType, $this->get(self::ATTR_INITIAL_VALUE))
            ];
        }
    }

    /**
     * @return IPhpType
     */
    abstract protected function buildPhpTypeOfInput();

    /**
     * @return IFieldProcessor[]
     */
    abstract protected function buildProcessors();

    /**
     * {@inheritDoc}
     */
    public function attrs()
    {
        return $this->attributes;
    }

    /**
     * {@inheritDoc}
     */
    public function has($attribute)
    {
        return isset($this->attributes[$attribute]);
    }

    /**
     * {@inheritDoc}
     */
    public function get($attribute)
    {
        return isset($this->attributes[$attribute]) ? $this->attributes[$attribute] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getAll(array $attributes)
    {
        return array_intersect_key($this->attributes, array_fill_keys($attributes, true)) + array_fill_keys($attributes, null);
    }

    /**
     * {@inheritDoc}
     */
    public function with($attribute, $value)
    {
        return $this->withAll([$attribute => $value]);
    }

    /**
     * {@inheritDoc}
     */
    public function withAll(array $attributes)
    {
        $clone             = clone $this;
        $clone->attributes = $attributes + $clone->attributes;
        $clone->initializeFromCurrentAttributes();

        return $clone;
    }

    /**
     * @return IFieldOptions|null
     */
    public function getOptions()
    {
        return $this->get(self::ATTR_OPTIONS);
    }

    /**
     * {@inheritDoc}
     */
    final public function getPhpTypeOfInput()
    {
        return $this->inputType;
    }

    /**
     * {@inheritDoc}
     */
    final public function getProcessors()
    {
        return $this->processors;
    }

    /**
     * {@inheritDoc}
     */
    final public function getProcessedPhpType()
    {
        return $this->processedType;
    }
}