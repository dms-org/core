<?php

namespace Dms\Core\Form\Object\Stage;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\IForm;
use Dms\Core\Form\IFormStage;
use Dms\Core\Form\InvalidFormSubmissionException;
use Dms\Core\Form\IStagedForm;
use Dms\Core\Form\Stage\IndependentFormStage;
use Dms\Core\Model\IDataTransferObject;
use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\TypedObject;

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
     * @var bool
     */
    private $isCloningDuplicate = false;

    /**
     * StagedFormObject constructor.
     */
    public function __construct()
    {
        $definition = new StagedFormObjectDefinition(static::definition());
        $this->defineForm($definition);
        $this->loadFinalizedStagedFormDefinition($definition->finalize($this));

        // TODO: Clean up handling of submission / stages
        //  parent::__construct();
    }

    /**
     * @param FinalizedStagedFormObjectDefinition $definition
     *
     * @return void
     */
    final public function loadFinalizedStagedFormDefinition(FinalizedStagedFormObjectDefinition $definition)
    {
        $this->formDefinition = $definition;
        $this->stagedForm     = $definition->getStagedForm();

        if (!$this->isCloningDuplicate) {
            $this->isCloningDuplicate = true;
            $this->stagedFormForClone = $definition->forInstance(clone $this)->getStagedForm();
            $this->isCloningDuplicate = false;
        }
    }

    /**
     * @inheritDoc
     */
    final protected function define(ClassDefinition $class)
    {
        $class->property($this->formDefinition)->ignore();
        $class->property($this->stagedForm)->ignore();
        $class->property($this->stagedFormForClone)->ignore();
        $class->property($this->isCloningDuplicate)->ignore();

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


    public function __clone()
    {
        if ($this->formDefinition) {
            $this->loadFinalizedStagedFormDefinition($this->formDefinition->forInstance($this));
        }
    }

    /**
     * @return FinalizedStagedFormObjectDefinition
     */
    final public function getStagedFormDefinition()
    {
        return $this->formDefinition;
    }

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
     * @return IForm
     */
    final public function getFirstForm()
    {
        return $this->stagedFormForClone->getFirstForm();
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
     * @return static
     */
    final public function submitFirstStage(array $firstStageSubmission)
    {
        return $this->formDefinition->withSubmittedFirstStage(
                $this->getFirstForm()->process($firstStageSubmission)
        );
    }

    /**
     * @inheritDoc
     * @return static
     */
    final public function withSubmittedFirstStage(array $processedFirstStageData)
    {
        return $this->formDefinition->withSubmittedFirstStage($processedFirstStageData);
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

        return $clone->submit($submission);
    }
}