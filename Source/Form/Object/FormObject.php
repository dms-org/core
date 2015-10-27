<?php

namespace Iddigital\Cms\Core\Form\Object;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\IForm;
use Iddigital\Cms\Core\Form\InvalidFormSubmissionException;
use Iddigital\Cms\Core\Model\IDataTransferObject;
use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\TypedObject;

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

    /**
     * @var array
     */
    private $initialValues;

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
        $class->property($this->initialValues)->ignore();

        return $this->defineClass($class);
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    abstract protected function defineClass(ClassDefinition $class);

    final public function loadFormObjectDefinition(FinalizedFormObjectDefinition $definition)
    {
        $this->formDefinition = $definition;
        $this->form           = $definition->getForm();
        $this->initialValues  = $this->form->getInitialValues();
    }

    /**
     * Gets the form definition for the called class.
     *
     * @return FinalizedFormObjectDefinition
     */
    final public function getFormDefinition()
    {
        return $this->formDefinition;
    }

    /**
     * Gets the form defined by the called form object.
     *
     * @return IForm
     */
    final public function getForm()
    {
        return $this->formDefinition->getForm();
    }

    /**
     * {@inheritDoc}
     */
    final public function getProcessors()
    {
        return $this->form->getProcessors();
    }

    /**
     * {@inheritDoc}
     */
    final public function getSections()
    {
        return $this->form->getSections();
    }

    /**
     * {@inheritDoc}
     */
    final  public function process(array $submission)
    {
        return $this->form->process($submission);
    }

    /**
     * {@inheritDoc}
     */
    final public function unprocess(array $processedSubmission)
    {
        return $this->form->unprocess($processedSubmission);
    }

    /**
     * {@inheritDoc}
     */
    final public function getFields()
    {
        return $this->form->getFields();
    }

    /**
     * {@inheritDoc}
     */
    final public function getFieldNames()
    {
        return $this->form->getFieldNames();
    }

    /**
     * {@inheritDoc}
     */
    final public function hasField($fieldName)
    {
        return $this->form->hasField($fieldName);
    }

    /**
     * {@inheritDoc}
     */
    final public function getField($fieldName)
    {
        return $this->form->getField($fieldName);
    }

    /**
     * @return array
     */
    final public function getInitialValues()
    {
        return $this->initialValues;
    }

    /**
     * @inheritDoc
     */
    final public function withInitialValues(array $initialValues)
    {
        return $this->form->withInitialValues($initialValues);
    }

    /**
     * {@inheritDoc}
     */
    final public function asStagedForm()
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

        foreach ($this->formDefinition->getPropertyInnerFormMap() as $property => $innerForm) {
            $fieldMap            = $innerForm->getEmbeddedFieldMap();
            $innerFormSubmission = [];

            foreach ($fieldMap as $field => $innerFormField) {
                $innerFormSubmission[$innerFormField] = $submission[$field];
            }

            $this->{$property} = $innerForm
                    ->getNewFormInstance()
                    ->buildFromProcessedSubmission($innerFormSubmission);
        }

        return $this;
    }
}