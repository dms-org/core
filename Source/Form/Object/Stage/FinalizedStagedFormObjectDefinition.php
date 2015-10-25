<?php

namespace Iddigital\Cms\Core\Form\Object\Stage;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\IStagedForm;
use Iddigital\Cms\Core\Form\Object\FinalizedFormObjectDefinition;
use Iddigital\Cms\Core\Form\Stage\DependentFormStage;
use Iddigital\Cms\Core\Form\Stage\IndependentFormStage;
use Iddigital\Cms\Core\Form\StagedForm;

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
        return new self($formObject, $this->defineStageCallbacks);
    }

    /**
     * @return IStagedForm
     */
    public function getStagedForm()
    {
        return $this->stagedForm;
    }

    /**
     * @param FormStageCallback[] $defineStageCallbacks
     *
     * @return StagedForm
     */
    private function loadStagedForm(array $defineStageCallbacks)
    {
        /** @var FormStageCallback $firstCallback */
        $firstCallback           = array_shift($defineStageCallbacks);
        $previousStageDefinition = $firstCallback->defineFormStage($this->formObject);
        $firstStage              = new IndependentFormStage($previousStageDefinition->getForm());
        $this->formDefinitions   = [$previousStageDefinition];
        $stages                  = [];

        foreach ($defineStageCallbacks as $defineStageCallback) {
            $stages[] = new DependentFormStage(
                    function (array $previousSubmission) use (
                            $defineStageCallback,
                            &$previousStageDefinition
                    ) {
                        foreach ($previousStageDefinition->getPropertyFieldMap() as $property => $field) {
                            $this->formObject->{$property} = $previousSubmission[$field];
                        }

                        /** @var FinalizedFormObjectDefinition $formObjectDefinition */
                        $formObjectDefinition    = $defineStageCallback->defineFormStage($this->formObject);
                        $this->formDefinitions[] = $formObjectDefinition;
                        $previousStageDefinition = $formObjectDefinition;

                        return $formObjectDefinition->getForm();
                    },
                    $defineStageCallback->getFieldsDefinedWithinStage(),
                    $defineStageCallback->getFieldsDependentOn()
            );
        }

        $this->stagedForm = new StagedForm($firstStage, $stages);
    }

    public function submit(array $submission)
    {
        $properties            = [];
        $this->formDefinitions = [$this->formDefinitions[0]];
        $processed             = $this->stagedForm->process($submission);

        foreach ($this->formDefinitions as $definition) {
            foreach ($definition->getPropertyFieldMap() as $property => $field) {
                $properties[$property] = $processed[$field];
            }
        }

        return $properties;
    }
}