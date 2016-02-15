<?php declare(strict_types = 1);

namespace Dms\Core\Form\Object\Stage;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\IStagedForm;
use Dms\Core\Form\Object\FinalizedFormObjectDefinition;
use Dms\Core\Form\Stage\DependentFormStage;
use Dms\Core\Form\Stage\IndependentFormStage;
use Dms\Core\Form\StagedForm;

/**
 * The finalized staged form object definition class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FinalizedStagedFormObjectDefinition
{
    /**
     * @var StagedFormObject
     */
    protected $formObject;

    /**
     * @var IStagedForm
     */
    protected $stagedForm;

    /**
     * @var FormStageCallback[]
     */
    private $defineStageCallbacks;

    /**
     * @var FinalizedFormObjectDefinition[]
     */
    protected $formDefinitions = [];

    /**
     * @var array
     */
    protected $knownProperties = [];

    /**
     * FinalizedStagedFormObjectDefinition constructor.
     *
     * @param StagedFormObject    $formObject
     * @param FormStageCallback[] $defineStageCallbacks
     */
    public function __construct(StagedFormObject $formObject, array $defineStageCallbacks)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'defineStageCallbacks', $defineStageCallbacks, FormStageCallback::class);

        $this->formObject           = $formObject;
        $this->defineStageCallbacks = $defineStageCallbacks;
        $this->loadStagedForm($defineStageCallbacks);
    }

    /**
     * @param StagedFormObject $formObject
     *
     * @return static
     */
    public function forInstance(StagedFormObject $formObject)
    {
        $self                  = new self($formObject, $this->defineStageCallbacks);
        $self->knownProperties = $this->knownProperties;

        return $self;
    }

    /**
     * @return IStagedForm
     */
    public function getStagedForm() : \Dms\Core\Form\IStagedForm
    {
        return $this->stagedForm;
    }

    /**
     * @param FormStageCallback[] $defineStageCallbacks
     *
     * @return void
     */
    private function loadStagedForm(array $defineStageCallbacks)
    {
        /** @var FormStageCallback $firstCallback */
        $currentStageIndex       = 0;
        $firstCallback           = array_shift($defineStageCallbacks);
        $previousStageDefinition = $firstCallback->defineFormStage($this->formObject);
        $firstStage              = new IndependentFormStage($previousStageDefinition->getForm());
        $this->formDefinitions   = [$currentStageIndex => $previousStageDefinition];
        $stages                  = [];

        foreach ($defineStageCallbacks as $defineStageCallback) {
            $currentStageIndex++;

            $stages[] = new DependentFormStage(
                    function (array $previousSubmission) use ($defineStageCallback, $currentStageIndex) {
                        $previousStageDefinition = $this->formDefinitions[$currentStageIndex - 1];
                        $this->loadSubmittedFieldsIntoObjectProperties($previousStageDefinition, $previousSubmission);

                        /** @var FinalizedFormObjectDefinition $formObjectDefinition */
                        $formObjectDefinition                      = $defineStageCallback->defineFormStage($this->formObject);
                        $this->formDefinitions[$currentStageIndex] = $formObjectDefinition;

                        return $formObjectDefinition->getForm();
                    },
                    $defineStageCallback->getFieldsDefinedWithinStage(),
                    $defineStageCallback->getFieldsDependentOn()
            );

            $currentStageIndex++;
        }

        $this->stagedForm = new StagedForm($firstStage, $stages);
    }

    protected function loadSubmittedFieldsIntoObjectProperties(FinalizedFormObjectDefinition $definition, array $processedSubmission)
    {
        $properties = [];
        $object     = new \ReflectionObject($this->formObject);

        foreach ($definition->getPropertyFieldMap() as $propertyName => $field) {
            $property = $object->getProperty($propertyName);
            $property->setAccessible(true);
            $property->setValue($this->formObject, $processedSubmission[$field]);

            $properties[$propertyName] = $processedSubmission[$field];
        }

        return $properties;
    }

    public function submit(array $submission)
    {
        $properties            = $this->knownProperties;
        $this->formDefinitions = [$this->formDefinitions[0]];
        $processed             = $this->stagedForm->process($submission);

        foreach ($this->formDefinitions as $definition) {
            foreach ($definition->getPropertyFieldMap() as $property => $field) {
                $properties[$property] = $processed[$field];
            }
        }

        return $properties;
    }

    public function withSubmittedFirstStage(array $processedFirstStageData)
    {
        $clone = $this->forInstance(clone $this->formObject);

        $clone->stagedForm = $clone->stagedForm->withSubmittedFirstStage($processedFirstStageData);

        $properties = $clone->loadSubmittedFieldsIntoObjectProperties($clone->formDefinitions[0], $processedFirstStageData);
        $clone->knownProperties += $properties;

        array_shift($clone->defineStageCallbacks);
        array_shift($clone->formDefinitions);

        $clone->formObject->loadFinalizedStagedFormDefinition($clone);

        return $clone->formObject;
    }
}