<?php declare(strict_types = 1);

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
    public function getClass() : ClassDefinition
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
    public function field(&$property) : FormObjectFieldNameBuilder
    {
        $typeDefiner  = $this->class->property($property);
        $propertyName = $this->loadTypeDefiner($typeDefiner);

        return FormObjectFieldNameBuilder::callback(function ($fieldName) use ($propertyName) {
            $this->fieldPropertyMap[$fieldName] = $propertyName;
        })->value($property);
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
    public function section(string $title, array $fields)
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
    public function finalize(FinalizedClassDefinition $class = null) : FinalizedFormObjectDefinition
    {
        return new FinalizedFormObjectDefinition(
                $class ?: $this->class->finalize(),
                $this->propertyFieldMap,
                $this->build()
        );
    }
}