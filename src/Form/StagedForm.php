<?php declare(strict_types = 1);

namespace Dms\Core\Form;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Form\Builder\Form as FormBuilder;
use Dms\Core\Form\Stage\DependentFormStage;
use Dms\Core\Form\Stage\IndependentFormStage;
use Dms\Core\Util\Debug;

/**
 * The staged form class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class StagedForm implements IStagedForm
{
    /**
     * @var IndependentFormStage
     */
    protected $firstStage;

    /**
     * @var IFormStage[]
     */
    protected $followingStages;

    /**
     * @var int[]
     */
    protected $fieldNameStageNumberMap = [];

    /**
     * @var array
     */
    protected $knownFormData = [];

    /**
     * StagedForm constructor.
     *
     * @param IndependentFormStage $firstStage
     * @param IFormStage[]         $followingStages
     * @param array                $knownFormData
     *
     * @throws InvalidArgumentException
     */
    public function __construct(IndependentFormStage $firstStage, array $followingStages, array $knownFormData = [])
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'stages', $followingStages, IFormStage::class);

        $this->firstStage      = $firstStage;
        $this->followingStages = $followingStages;
        $this->knownFormData   = $knownFormData;

        $stageNumber       = 1;
        $definedFieldNames = array_fill_keys($this->firstStage->getDefinedFieldNames(), $stageNumber);

        foreach ($this->followingStages as $stage) {
            if (!$stage->areAllFieldsRequired()) {
                foreach ($stage->getRequiredFieldNames() as $requiredFieldName) {
                    if (!isset($definedFieldNames[$requiredFieldName]) && !array_key_exists($requiredFieldName, $this->knownFormData)) {
                        throw InvalidArgumentException::format(
                            'Invalid required field in stage %d: expecting one of previously defined fields (%s), \'%s\' given',
                            $stageNumber, Debug::formatValues(array_keys($definedFieldNames)), $requiredFieldName
                        );
                    }
                }
            }

            $stageNumber++;
            $definedFieldNames += array_fill_keys($stage->getDefinedFieldNames(), $stageNumber);
        }

        $this->fieldNameStageNumberMap = $definedFieldNames;
    }

    /**
     * @return IndependentFormStage
     */
    public function getFirstStage() : Stage\IndependentFormStage
    {
        return $this->firstStage;
    }

    /**
     * @param int $stageNumber
     *
     * @return IFormStage
     * @throws InvalidArgumentException
     */
    public function getStage(int $stageNumber) : IFormStage
    {
        InvalidArgumentException::verify(
            $stageNumber > 0 && $stageNumber <= $this->getAmountOfStages(),
            'Stage number must be between %s and %s, %s given', 1, $this->getAmountOfStages(), $stageNumber
        );

        if ($stageNumber === 1) {
            return $this->firstStage;
        } else {
            return $this->followingStages[$stageNumber - 2];
        }
    }

    /**
     * @inheritDoc
     */
    public function getStageWithFieldName(string $fieldName) : IFormStage
    {
        $allFieldNames = [];

        foreach ($this->getAllStages() as $stage) {
            $definedFieldNames = $stage->getDefinedFieldNames();

            if (in_array($fieldName, $definedFieldNames, true)) {
                return $stage;
            }

            $allFieldNames = array_merge($allFieldNames, $definedFieldNames);
        }

        throw InvalidArgumentException::format(
            'Invalid call to %s: invalid field name, expecting one of (%s), \'%s\' given',
            __METHOD__, Debug::formatValues($allFieldNames), $fieldName
        );
    }

    /**
     * @return IFormStage[]
     */
    public function getFollowingStages() : array
    {
        return $this->followingStages;
    }

    /**
     * @return IFormStage[]
     */
    public function getAllStages() : array
    {
        return array_merge([$this->firstStage], $this->followingStages);
    }

    /**
     * @return int
     */
    public function getAmountOfStages() : int
    {
        return 1 + count($this->followingStages);
    }

    /**
     * @inheritDoc
     */
    public function getFirstForm() : IForm
    {
        return $this->firstStage->loadForm();
    }

    /**
     * @inheritDoc
     */
    public function getRequiredFieldGroupedByStagesForStage(int $stageNumber)
    {
        $requiredFields = [];
        $this->getRequiredFieldsForStage($stageNumber, $requiredFields);
        ksort($requiredFields, SORT_ASC);

        return $requiredFields;
    }

    protected function getRequiredFieldsForStage($stageNumber, array &$requiredFields)
    {
        if (isset($requiredFields[$stageNumber]) && $requiredFields[$stageNumber] === '*') {
            return;
        }

        $stage = $this->getStage($stageNumber);

        if ($stage->areAllFieldsRequired()) {
            foreach (range(1, $stageNumber - 1) as $previousStageNumber) {
                $previousStage = $this->getStage($previousStageNumber);

                $requiredFields[$previousStageNumber] =
                    $previousStage instanceof IndependentFormStage
                        ? $previousStage->getDefinedFieldNames()
                        : '*';
            }
        } else {
            foreach ($stage->getRequiredFieldNames() as $requiredFieldName) {
                if (array_key_exists($requiredFieldName, $this->knownFormData)) {
                    continue;
                }

                $previousStageNumber = $this->fieldNameStageNumberMap[$requiredFieldName];

                if (isset($requiredFields[$previousStageNumber])) {
                    if ($requiredFields[$previousStageNumber] !== '*'
                        && !in_array($requiredFieldName, $requiredFields[$previousStageNumber], true)
                    ) {
                        $requiredFields[$previousStageNumber][] = $requiredFieldName;
                    }
                } else {
                    $requiredFields[$previousStageNumber] = [$requiredFieldName];
                }

                $this->getRequiredFieldsForStage($previousStageNumber, $requiredFields);
            }
        }
    }

    protected function getStageNumberFromStage(IFormStage $stage)
    {
        if ($stage === $this->firstStage) {
            return 1;
        } else {
            return array_search($stage, $this->followingStages, true) + 2;
        }
    }

    /**
     * @inheritDoc
     */
    public function tryLoadFormForStage(int $stageNumber, array $previousStagesSubmission, bool $isProcessed = false)
    {
        try {
            return $this->getFormForStage($stageNumber, $previousStagesSubmission, $isProcessed);
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function getFormForStage(int $stageNumber, array $previousStagesSubmission, bool $isProcessed = false) : IForm
    {
        $finalStage = $this->getStage($stageNumber);
        if ($finalStage instanceof IndependentFormStage) {
            return $finalStage->loadForm();
        }

        $requiredFields      = $this->getRequiredFieldGroupedByStagesForStage($stageNumber);
        $processedSubmission = $this->knownFormData;

        foreach ($requiredFields as $previousStageNumber => $requiredFieldNames) {
            $stage        = $this->getStage($previousStageNumber);
            $formForStage = $stage->loadForm($processedSubmission);

            if ($requiredFieldNames === '*') {
                $requiredFieldNames = $formForStage->getFieldNames();
            }

            $previousStagesSubmission = $previousStagesSubmission + array_fill_keys($requiredFieldNames, null);
            
            $fieldsToProcess = [];

            foreach ($formForStage->getFields() as $field) {
                if (in_array($field->getName(), $requiredFieldNames, true)) {
                    $fieldsToProcess[] = $field;
                }
            }

            $formToProcess = FormBuilder::create()->section('Data', $fieldsToProcess)->build();

            if (!$isProcessed) {
                $processedSubmission += $formToProcess->process($previousStagesSubmission);
            } else {
                $processedSubmissionForForm = array_intersect_key($previousStagesSubmission, $formToProcess->getFields());
                $formToProcess->validateProcessedValues($processedSubmissionForForm);
                $processedSubmission += $processedSubmissionForForm;
            }
        }

        return $this->getStage($stageNumber)->loadForm($processedSubmission);
    }

    /**
     * {@inheritdoc]
     */
    public function submitFirstStage(array $firstStageSubmission)
    {
        return $this->withSubmittedFirstStage(
            $this->firstStage->loadForm()->process($firstStageSubmission)
        );
    }

    /**
     * {@inheritdoc]
     */
    public function withSubmittedFirstStage(array $processedFirstStageData)
    {
        if ($this->getAmountOfStages() === 1) {
            throw InvalidOperationException::format(
                'Invalid call to %s: staged form only contains one stage',
                __METHOD__
            );
        }

        $this->firstStage->loadForm()
            ->validateProcessedValues($processedFirstStageData);

        $processedFirstStageData += $this->knownFormData;

        $newFirstStage = new IndependentFormStage($this->getStage(2)->loadForm($processedFirstStageData));
        /** @var IFormStage[] $newFollowingStages */
        $newFollowingStages = array_slice($this->followingStages, 1);
        $knownFieldNames    = array_keys($processedFirstStageData);

        foreach ($newFollowingStages as $key => $followingStage) {
            if ($followingStage instanceof DependentFormStage && $followingStage->getRequiredFieldNames() !== null) {
                $knowsAllRequiredFields = empty(array_diff($followingStage->getRequiredFieldNames(), $knownFieldNames));

                if ($knowsAllRequiredFields) {
                    $newFollowingStages[$key] = new IndependentFormStage($followingStage->loadForm($processedFirstStageData));
                }
            }
        }

        $stagedForm = new StagedForm($newFirstStage, $newFollowingStages, $processedFirstStageData);

        return $stagedForm;
    }

    /**
     * {@inheritdoc]
     */
    public function process(array $submission) : array
    {
        $processed = $this->knownFormData;
        $processed += $this->firstStage->loadForm()->process($submission);

        foreach ($this->followingStages as $stage) {
            $currentProcessedStage = $stage->loadForm($processed)->process($submission);

            $processed += $currentProcessedStage;
        }

        return $processed;
    }

    /**
     * {@inheritdoc]
     */
    public function unprocess(array $processedSubmission) : array
    {
        $unprocessed = [];

        /** @var IFormStage[] $reversedStages */
        $reversedStages = array_reverse($this->followingStages);

        foreach ($reversedStages as $stage) {
            $unprocessed[] = $this->unprocessStage($stage, $processedSubmission);
        }

        $unprocessed[] = $this->unprocessStage($this->firstStage, $processedSubmission);

        $unprocessedSubmission = [];
        foreach (array_reverse($unprocessed) as $unprocessedStage) {
            $unprocessedSubmission += $unprocessedStage;
        }

        return $unprocessedSubmission;
    }

    private function unprocessStage(IFormStage $stage, array $processedSubmission)
    {
        $form = $stage->loadForm($processedSubmission);

        return $form->unprocess(array_intersect_key(
            $processedSubmission,
            $form->getFields()
        ));
    }
}