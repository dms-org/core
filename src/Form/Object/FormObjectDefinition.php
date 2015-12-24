<?php

namespace Dms\Core\Form\Object;

use Dms\Core\Form\Builder\Form as FormBuilder;
use Dms\Core\Form\IField;
use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\FinalizedClassDefinition;
use Dms\Core\Model\Object\InvalidPropertyDefinitionException;
use Dms\Core\Model\Object\PropertyTypeDefiner;

/**
 * The form object definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FormObjectDefinition extends FormBuilder
{
    /**
     * @var string[]
     */
    private $propertyFieldMap = [];

    /**
     * @var string[]
     */
    private $fieldPropertyMap = [];

    /**
     * @var InnerFormDefinition[]
     */
    private $propertyInnerFormMap = [];

    /**
     * @var ClassDefinition
     */
    private $class;

    /**
     * @var PropertyTypeDefiner
     */
    private $propertyTypeDefinerMap = [];

    /**
     * FormObjectDefinition constructor.
     *
     * @param ClassDefinition $class
     */
    public function __construct(ClassDefinition $class)
    {
        parent::__construct();

        $this->class = $class;

        // Have to increase the backtrace level for property
        // definition errors as the actual property definitions
        // will happen at the FormObjectDefinition::field(...)
        // which delegates to ClassDefinition::property() so it
        // would be more helpful to the user to show the outer
        // call rather than the trace pointing to this file.
        $this->class->setPropertyDefinitionTraceLevel(2);
    }

    /**
     * @return ClassDefinition
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Binds the property to the defined field on the form object.
     *
     * @param mixed $property
     *
     * @return FormObjectFieldNameBuilder
     * @throws InvalidPropertyDefinitionException
     */
    public function field(&$property)
    {
        $typeDefiner  = $this->class->property($property);
        $propertyName = $this->loadTypeDefiner($typeDefiner);

        return FormObjectFieldNameBuilder::callback(function ($fieldName) use ($propertyName) {
            $this->fieldPropertyMap[$fieldName] = $propertyName;
        })->value($property);
    }

    /**
     * Binds the supplied property to another form object.
     *
     * @param mixed $property
     *
     * @return PropertyFormBinding
     */
    public function bind(&$property)
    {
        $typeDefiner  = $this->class->property($property);
        $propertyName = $this->loadTypeDefiner($typeDefiner);

        return new PropertyFormBinding(
                $typeDefiner,
                function (FormObject $innerForm) use ($propertyName, &$property) {
                    $this->loadInnerForm($propertyName, $innerForm);
                    $property = $innerForm;
                }
        );
    }

    private function loadInnerForm($property, FormObject $formObject)
    {
        $definition        = $formObject->getFormDefinition();
        $prefix            = $property . '_';
        $innerFormFieldMap = [];

        foreach ($definition->getForm()->getSections() as $section) {
            $prefixedFields = [];

            foreach ($section->getFields() as $field) {
                $prefixedFieldName                     = $prefix . $field->getName();
                $prefixedFields[]                      = $field->withName($prefixedFieldName);
                $innerFormFieldMap[$prefixedFieldName] = $field->getName();
            }

            parent::section($section->getTitle(), $prefixedFields);
        }

        $this->propertyInnerFormMap[$property] = new InnerFormDefinition($property, $formObject, $innerFormFieldMap);
    }

    private function loadTypeDefiner(PropertyTypeDefiner $typeDefiner)
    {
        $propertyName                                = $typeDefiner->getDefinition()->getName();
        $this->propertyTypeDefinerMap[$propertyName] = $typeDefiner;

        return $propertyName;
    }

    /**
     * {@inheritDoc}
     * @throws InvalidFieldDefinitionException
     */
    public function section($title, array $fields)
    {
        $fields = $this->buildAllFields($fields);

        foreach ($fields as $field) {
            $fieldName = $field->getName();

            if (!isset($this->fieldPropertyMap[$fieldName])) {
                throw new InvalidFieldDefinitionException($this->class->getClass(), $fieldName);
            }

            $propertyName = $this->fieldPropertyMap[$fieldName];

            $this->inferPropertyType($this->propertyTypeDefinerMap[$propertyName], $field);
            $this->propertyFieldMap[$propertyName] = $fieldName;
        }

        parent::section($title, $fields);

        return $this;
    }

    /**
     * @param PropertyTypeDefiner $typeDefiner
     * @param IField              $field
     *
     * @return void
     */
    private function inferPropertyType(PropertyTypeDefiner $typeDefiner, IField $field)
    {
        $typeDefiner->asType($field->getProcessedType());
    }

    /**
     * Finalizes the form definition.
     *
     * @param FinalizedClassDefinition $class
     *
     * @return FinalizedFormObjectDefinition
     * @throws \Dms\Core\Model\Object\IncompleteClassDefinitionException
     */
    public function finalize(FinalizedClassDefinition $class = null)
    {
        return new FinalizedFormObjectDefinition(
                $class ?: $this->class->finalize(),
                $this->propertyFieldMap,
                $this->propertyInnerFormMap,
                $this->build()
        );
    }
}