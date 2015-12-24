<?php

namespace Dms\Core\Common\Crud\Definition\Form;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Form\Binding\Field\CustomFieldBinding;
use Dms\Core\Form\Binding\Field\FieldPropertyBinding;
use Dms\Core\Form\Binding\Field\GetterSetterMethodBinding;
use Dms\Core\Form\Binding\IFieldBinding;
use Dms\Core\Form\IField;
use Dms\Core\Model\Object\FinalizedClassDefinition;

/**
 * The field binding definer.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FormFieldBindingDefiner
{
    /**
     * @var FinalizedClassDefinition
     */
    protected $class;

    /**
     * @var IField
     */
    protected $field;

    /**
     * FormFieldBindingDefiner constructor.
     *
     * @param FinalizedClassDefinition $class
     * @param IField                   $field
     */
    public function __construct(FinalizedClassDefinition $class, IField $field)
    {
        $this->class = $class;
        $this->field = $field;
    }

    /**
     * Binds the field using the supplied form binding.
     *
     * @param IFieldBinding $binding
     *
     * @return FormFieldBindingDefinition
     */
    public function bindTo(IFieldBinding $binding)
    {
        return new FormFieldBindingDefinition($this->field, $binding);
    }

    /**
     * Binds the field to the supplied property name.
     *
     * @param string $name
     *
     * @return FormFieldBindingDefinition
     * @throws TypeMismatchException
     * @throws InvalidArgumentException
     */
    public function bindToProperty($name)
    {
        if (!$this->field->getProcessedType()->isSubsetOf($this->class->getProperty($name)->getType())) {
            throw TypeMismatchException::format(
                    'Cannot bind property %s::$%s to field \'%s\': field type must be compatible with property type %s, %s given',
                    $this->class->getClassName(), $name, $this->field->getName(),
                    $this->class->getProperty($name)->getType()->asTypeString(), $this->field->getProcessedType()->asTypeString()
            );
        }

        return $this->bindTo(new FieldPropertyBinding($this->field->getName(), $this->class, $name));
    }

    /**
     * Binds the field to the supplied getter/setter methods.
     *
     * @param string $getterMethodName
     * @param string $setterMethodName
     *
     * @return FormFieldBindingDefinition
     */
    public function bindToGetSetMethods($getterMethodName, $setterMethodName)
    {
        return $this->bindTo(new GetterSetterMethodBinding(
                $this->field->getName(),
                $this->class->getClassName(),
                $getterMethodName,
                $setterMethodName
        ));
    }

    /**
     * Binds the field to the supplied getter/setter callbacks.
     *
     * Example:
     * <code>
     * ->bindToCustom(function (SomeEntity $entity) {
     *      return $entity->someProperty;
     * }, function (SomeEntity $entity, $input) {
     *      $entity->someProperty = $input;
     * })
     * </code>
     *
     * @param callable $getterCallback
     * @param callable $setterCallback
     *
     * @return FormFieldBindingDefinition
     */
    public function bindToCallbacks(callable $getterCallback, callable $setterCallback)
    {
        return $this->bindTo(new CustomFieldBinding(
                $this->field->getName(),
                $this->class->getClassName(),
                $getterCallback,
                $setterCallback
        ));
    }

    /**
     * Adds the field without a binding.
     *
     * @return FormFieldBindingDefinition
     */
    public function withoutBinding()
    {
        return new FormFieldBindingDefinition($this->field);
    }
}