<?php

namespace Dms\Core\Form\Field\Type;

use Dms\Core\Form\Field\Processor\Validator\TypeValidator;
use Dms\Core\Form\IFieldOptions;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Form\IFieldType;
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
    const ATTR_DEFAULT = 'default';
    const ATTR_OPTIONS = 'options';

    /**
     * @var array
     */
    protected $attributes = [];

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
        $this->loadTypes();
    }

    /**
     * @return void
     */
    private function loadTypes()
    {
        $this->inputType = $this->buildPhpTypeOfInput()->nullable();

        $this->processors = $this->buildProcessors();

        if (!($this->inputType instanceof MixedType)) {
            array_unshift($this->processors, new TypeValidator($this->inputType));
        }

        /** @var IFieldProcessor|false $lastProcessor */
        $lastProcessor = end($this->processors);

        $this->processedType = $lastProcessor ? $lastProcessor->getProcessedType() : $this->inputType;

        if ($this->get(self::ATTR_REQUIRED)) {
            $this->processedType = $this->processedType->nonNullable();
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
    public function with($attribute, $value)
    {
        return $this->withAll([$attribute => $value]);
    }

    /**
     * {@inheritDoc}
     */
    public function withAll(array $attributes)
    {
        $clone                         = clone $this;
        $clone->attributes = $attributes + $clone->attributes ;
        $clone->loadTypes();

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