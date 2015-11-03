<?php

namespace Iddigital\Cms\Core\Form\Object\Stage;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\IFormStage;
use Iddigital\Cms\Core\Form\InvalidFormSubmissionException;
use Iddigital\Cms\Core\Form\IStagedForm;
use Iddigital\Cms\Core\Form\Stage\IndependentFormStage;
use Iddigital\Cms\Core\Model\IDataTransferObject;
use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\TypedObject;

/**
 * The staged form object base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class StagedFormObject extends TypedObject implements IDataTransferObject, IStagedForm
{
    /**
     * @var FinalizedStagedFormObjectDefinition
     */
    private $formDefinition;

    /**
     * @var IStagedForm
     */
    private $stagedForm;

    /**
     * @var IStagedForm
     */
    private $stagedFormForClone;

    /**
     * StagedFormObject constructor.
     */
    public function __construct()
    {
        $definition = new StagedFormObjectDefinition(static::definition());
        $this->defineForm($definition);
        $this->formDefinition     = $definition->finalize($this);
        $this->stagedForm         = $this->formDefinition->getStagedForm();
        $this->stagedFormForClone = $this->formDefinition->forInstance(clone $this)->getStagedForm();

        // TODO: Clean up handling of submission / stages
        //  parent::__construct();
    }

    /**
     * @inheritDoc
     */
    final protected function define(ClassDefinition $class)
    {
        $class->property($this->formDefinition)->ignore();
        $class->property($this->stagedForm)->ignore();
        $class->property($this->stagedFormForClone)->ignore();

        return $this->defineClass($class);
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    abstract protected function defineClass(ClassDefinition $class);

    /**
     * Defines the staged form.
     *
     * @param StagedFormObjectDefinition $form
     */
    abstract protected function defineForm(StagedFormObjectDefinition $form);

    /**
     * @return IndependentFormStage
     */
    final public function getFirstStage()
    {
        return $this->stagedFormForClone->getFirstStage();
    }

    /**
     * @return IFormStage[]
     */
    final public function getFollowingStages()
    {
        return $this->stagedFormForClone->getFollowingStages();
    }

    /**
     * @inheritDoc
     */
    final public function getAllStages()
    {
        return $this->stagedFormForClone->getAllStages();
    }

    /**
     * @return int
     */
    final public function getAmountOfStages()
    {
        return $this->stagedFormForClone->getAmountOfStages();
    }

    /**
     * @inheritDoc
     */
    final public function getStageWithFieldName($fieldName)
    {
        return $this->stagedFormForClone->getStageWithFieldName($fieldName);
    }

    /**
     * @inheritDoc
     */
    final public function getRequiredFieldGroupedByStagesForStage($stageNumber)
    {
        return $this->stagedFormForClone->getRequiredFieldGroupedByStagesForStage($stageNumber);
    }

    /**
     * @param int $stageNumber The 1-based stage number
     *
     * @return IFormStage
     * @throws InvalidArgumentException If it is out of range
     */
    final public function getStage($stageNumber)
    {
        return $this->stagedFormForClone->getStage($stageNumber);
    }

    /**
     * @inheritDoc
     */
    final public function getFormForStage($stageNumber, array $previousStagesSubmission)
    {
        return $this->stagedFormForClone->getFormForStage($stageNumber, $previousStagesSubmission);
    }

    /**
     * @inheritDoc
     */
    final public function withSubmittedFirstStage(array $processedFirstStageData)
    {
        return $this->stagedFormForClone->withSubmittedFirstStage($processedFirstStageData);
    }

    /**
     * Processes the forms submission through all stages.
     *
     * @param array $submission
     *
     * @return array
     * @throws InvalidFormSubmissionException
     */
    final public function process(array $submission)
    {
        return $this->stagedFormForClone->process($submission);
    }

    /**
     * Unprocesses the form submission back into the initial format.
     *
     * @param array $processedSubmission
     *
     * @return array
     */
    final public function unprocess(array $processedSubmission)
    {
        return $this->stagedFormForClone->unprocess($processedSubmission);
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
        $properties = $this->formDefinition->submit($submission);

        parent::__construct();
        $this->hydrate($properties);

        return $this;
    }

    /**
     * Returns a new form object filled with the data from the supplied submission.
     *
     * @param array $submission
     *
     * @return static
     * @throws InvalidFormSubmissionException
     */
    final public function submitNew(array $submission)
    {
        $clone = clone $this;

        $clone->formDefinition = $clone->formDefinition->forInstance($clone);

        return $clone->submit($submission);
    }
}