<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud\Definition\Form;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Form\Binding\Accessor\CustomFieldAccessor;
use Dms\Core\Form\Binding\Accessor\FieldPropertyAccessor;
use Dms\Core\Form\Binding\Accessor\GetterSetterMethodAccessor;
use Dms\Core\Form\Binding\Accessor\IFieldAccessor;
use Dms\Core\Form\Binding\FieldBinding;
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
     * Binds the field using the supplied field accessor.
     *
     * @param IFieldAccessor $accessor
     *
     * @return FormFieldBindingDefinition
     */
    public function bindTo(IFieldAccessor $accessor) : FormFieldBindingDefinition
    {
        return new FormFieldBindingDefinition($this->field, new FieldBinding($this->field->getName(), $accessor));
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
    public function bindToProperty(string $name) : FormFieldBindingDefinition
    {
        if (!$this->field->getProcessedType()->isSubsetOf($this->class->getProperty($name)->getType())) {
            throw TypeMismatchException::format(
                    'Cannot bind property %s::$%s to field \'%s\': field type must be compatible with property type %s, %s given',
                    $this->class->getClassName(), $name, $this->field->getName(),
                    $this->class->getProperty($name)->getType()->asTypeString(), $this->field->getProcessedType()->asTypeString()
            );
        }

        return $this->bindTo(new FieldPropertyAccessor($this->class, $name));
    }

    /**
     * Binds the field to the supplied getter/setter methods.
     *
     * @param string $getterMethodName
     * @param string $setterMethodName
     *
     * @return FormFieldBindingDefinition
     */
    public function bindToGetSetMethods(string $getterMethodName, string $setterMethodName) : FormFieldBindingDefinition
    {
        return $this->bindTo(new GetterSetterMethodAccessor(
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
    public function bindToCallbacks(callable $getterCallback, callable $setterCallback) : FormFieldBindingDefinition
    {
        return $this->bindTo(new CustomFieldAccessor(
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
    public function withoutBinding() : FormFieldBindingDefinition
    {
        return new FormFieldBindingDefinition($this->field);
    }
}