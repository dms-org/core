<?php declare(strict_types = 1);

namespace Dms\Core\Form\Object;

use Dms\Core\Form\IField;
use Dms\Core\Form\IForm;
use Dms\Core\Form\InvalidFormSubmissionException;
use Dms\Core\Form\IStagedForm;
use Dms\Core\Model\IDataTransferObject;
use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\TypedObject;

/**
 * The form object base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class FormObject extends TypedObject implements IDataTransferObject, IForm
{
    /**
     * @var FinalizedFormObjectDefinition
     */
    private $formDefinition;

    /**
     * @var IForm
     */
    private $form;

    public function __construct(FinalizedFormObjectDefinition $definition)
    {
        parent::__construct();
        $this->loadFormObjectDefinition($definition);
    }

    /**
     * @inheritDoc
     */
    final protected function define(ClassDefinition $class)
    {
        $class->property($this->formDefinition)->ignore();
        $class->property($this->form)->ignore();

        return $this->defineClass($class);
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    abstract protected function defineClass(ClassDefinition $class);

    final public function loadFormObjectDefinition(FinalizedFormObjectDefinition $definition, bool $loadInitialValues = true)
    {
        $this->formDefinition = $definition;
        $this->form           = $definition->getForm();

        if ($loadInitialValues) {
            $values = $this->form->getInitialValues();

            foreach ($this->formDefinition->getPropertyFieldMap() as $property => $field) {
                if (isset($values[$field])) {
                    $this->{$property} = $values[$field];
                }
            }
        }
    }

    /**
     * Gets the form definition for the called class.
     *
     * @return FinalizedFormObjectDefinition
     */
    final public function getFormDefinition() : FinalizedFormObjectDefinition
    {
        return $this->formDefinition;
    }

    /**
     * Gets the form defined by the called form object.
     *
     * @return IForm
     */
    final public function getForm() : IForm
    {
        return $this->formDefinition->getForm();
    }

    /**
     * {@inheritDoc}
     */
    final public function getProcessors() : array
    {
        return $this->form->getProcessors();
    }

    /**
     * {@inheritDoc}
     */
    final public function getSections() : array
    {
        return $this->form->getSections();
    }

    /**
     * {@inheritDoc}
     */
    final public function validateProcessedValues(array $processedSubmission)
    {
        $this->form->validateProcessedValues($processedSubmission);
    }

    /**
     * {@inheritDoc}
     */
    final public function process(array $submission) : array
    {
        return $this->form->process($submission);
    }

    /**
     * {@inheritDoc}
     */
    final public function unprocess(array $processedSubmission) : array
    {
        return $this->form->unprocess($processedSubmission);
    }

    /**
     * {@inheritDoc}
     */
    final public function getFields() : array
    {
        return $this->form->getFields();
    }

    /**
     * {@inheritDoc}
     */
    final public function getFieldNames() : array
    {
        return $this->form->getFieldNames();
    }

    /**
     * {@inheritDoc}
     */
    final public function hasField(string $fieldName) : bool
    {
        return $this->form->hasField($fieldName);
    }

    /**
     * {@inheritDoc}
     */
    final public function getField(string $fieldName) : IField
    {
        return $this->form->getField($fieldName);
    }

    /**
     * @return array
     */
    final public function getInitialValues() : array
    {
        $initialValues = [];
        $properties    = $this->toArray();

        foreach ($this->formDefinition->getPropertyFieldMap() as $property => $field) {
            $initialValues[$field] = $properties[$property];
        }

        return $initialValues;
    }

    /**
     * @inheritDoc
     */
    final public function withInitialValues(array $initialProcessedValues) : IForm
    {
        $clone = clone $this;

        $clone->loadFormObjectDefinition($clone->formDefinition->withInitialValues($initialProcessedValues));

        return $clone;
    }

    /**
     * @inheritDoc
     */
    final public function withFieldNames(array $fieldNameMap) : IForm
    {
        $clone = clone $this;

        $clone->loadFormObjectDefinition($clone->formDefinition->withFieldNames($fieldNameMap));

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    final public function asStagedForm() : IStagedForm
    {
        return $this->form->asStagedForm();
    }

    /**
     * Returns a new form object with the data from the supplied submission.
     *
     * @param array $submission
     *
     * @return static
     * @throws InvalidFormSubmissionException
     */
    final public function submitNew(array $submission)
    {
        $clone = clone $this;

        return $clone->submit($submission);
    }

    /**
     * Fills the form object with the data from the supplied submission.
     *
     * @param array $submission
     *
     * @return static
     * @throws InvalidFormSubmissionException
     */
    final public function submit(array $submission)
    {
        return $this->buildFromProcessedSubmission($this->process($submission));
    }

    private function buildFromProcessedSubmission(array $submission)
    {
        foreach ($this->formDefinition->getPropertyFieldMap() as $property => $field) {
            $this->{$property} = $submission[$field];
        }

        return $this->withInitialValues($submission);
    }
}