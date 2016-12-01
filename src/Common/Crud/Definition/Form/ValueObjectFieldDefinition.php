<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud\Definition\Form;

use Dms\Core\Common\Crud\Form\FormWithBinding;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Exception\InvalidReturnValueException;
use Dms\Core\Form\Binding\IFieldBinding;
use Dms\Core\Form\Field\Builder\FieldBuilderBase;
use Dms\Core\Form\FormSection;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFormSection;
use Dms\Core\Model\IValueObject;
use Dms\Core\Model\Object\FinalizedClassDefinition;
use Dms\Core\Model\Object\TypedObject;
use Dms\Core\Model\Object\ValueObject;
use Dms\Core\Util\Debug;

/**
 * The value object field definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ValueObjectFieldDefinition
{
    /**
     * @var FinalizedClassDefinition|null
     */
    private $class;

    /**
     * @var IFormSection[]
     */
    protected $formSections = [];

    /**
     * @var IFieldBinding[]
     */
    protected $fieldBindings = [];

    /**
     * @var callable
     */
    protected $createObjectCallback;

    /**
     * ValueObjectFieldDefinition constructor.
     */
    public function __construct()
    {
        $this->createObjectCallback = function () {
            return $this->class->newCleanInstance();
        };
    }

    /**
     * Defines the value object type.
     *
     * @param string $valueObjectClass
     *
     * @throws InvalidArgumentException
     */
    public function bindTo(string $valueObjectClass)
    {
        /** @var string|TypedObject $valueObjectClass */
        if (!is_subclass_of($valueObjectClass, IValueObject::class, true)) {
            throw InvalidArgumentException::format(
                'Invalid value object class supplied to %s: expecting subclass of %s, %s given',
                __METHOD__, IValueObject::class, $valueObjectClass
            );
        }

        $this->class = $valueObjectClass::definition();
    }

    /**
     * Defines a form section with the supplied form field bindings.
     *
     * Example:
     * <code>
     * $form->section('Details', [
     *      $form->field(
     *          Field::name('name')->label('Name')->string()->required()
     *      )->bindToProperty('name')
     * ]);
     * </code>
     *
     * @param string                       $title
     * @param FormFieldBindingDefinition[] $fieldBindings
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function section(string $title, array $fieldBindings)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'fieldBindings', $fieldBindings, FormFieldBindingDefinition::class);

        $fields = [];
        foreach ($fieldBindings as $fieldBinding) {
            $fields[] = $fieldBinding->getField();

            if ($fieldBinding->hasBinding()) {
                $this->fieldBindings[] = $fieldBinding->getBinding();
            }
        }

        $this->formSections[] = new FormSection($title, $fields);
    }

    /**
     * Defines continues the preview form section with the supplied form field bindings.
     *
     * Example:
     * <code>
     * $form->section('Details', [
     *      $form->field(
     *          Field::name('name')->label('Name')->string()->required()
     *      )->bindToProperty('name')
     * ]);
     * </code>
     *
     * @param FormFieldBindingDefinition[] $fieldBindings
     *
     * @return void
     * @throws InvalidArgumentException
     * @throws InvalidOperationException
     */
    public function continueSection(array $fieldBindings)
    {
        $this->section('', $fieldBindings);
    }

    /**
     * Defines a field in the current form section.
     *
     * @param IField|FieldBuilderBase $field
     *
     * @return FormFieldBindingDefiner
     */
    public function field($field) : FormFieldBindingDefiner
    {
        if ($field instanceof FieldBuilderBase) {
            $field = $field->build();
        }

        InvalidArgumentException::verifyInstanceOf(__METHOD__, 'field', $field, IField::class);

        return new FormFieldBindingDefiner($this->class, $field);
    }

    protected function buildFormForCurrentStage() : FormWithBinding
    {
        return new FormWithBinding(
            $this->formSections,
            [],
            $this->class->getClassName(),
            $this->fieldBindings
        );
    }

    /**
     * Defines a callback to create new instances of the object.
     * The callback can either return an instance or the class
     * name of the object of which to construct.
     *
     * @return ObjectConstructorCallbackDefiner
     */
    public function createObjectType() : ObjectConstructorCallbackDefiner
    {
        return new ObjectConstructorCallbackDefiner($this->class, function (callable $typeCallback) {
            $this->createObjectCallback = function (array $input) use ($typeCallback) {
                $className = $this->class->getClassName();

                /** @var ValueObject $valueObject */
                $valueObject = $typeCallback($input);

                if (!($valueObject instanceof $className)) {
                    throw InvalidReturnValueException::format(
                        'Invalid create object callback return value: expecting class compatible with %s, %s given',
                        $className, is_string($valueObject) ? $valueObject : Debug::getType($valueObject)
                    );
                }

                return $valueObject;
            };
        });
    }

    /**
     * @return FinalizedValueObjectFieldDefinition
     * @throws InvalidOperationException
     */
    public function finalize() : FinalizedValueObjectFieldDefinition
    {
        if (!$this->class) {
            throw InvalidOperationException::format('Cannot finalize value object field definition: type has not been set, has $form->bindTo(...) been called?');
        }

        return new FinalizedValueObjectFieldDefinition(
            $this->class,
            new FormWithBinding(
                $this->formSections,
                [],
                $this->class->getClassName(),
                $this->fieldBindings
            ),
            $this->createObjectCallback
        );
    }
}