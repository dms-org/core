<?php

namespace Iddigital\Cms\Core\Form;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\Stage\IndependentFormStage;
use Iddigital\Cms\Core\Util\Debug;

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
     * StagedForm constructor.
     *
     * @param IndependentFormStage $firstStage
     * @param IFormStage[]         $following
     *
     * @throws InvalidArgumentException
     */
    public function __construct(IndependentFormStage $firstStage, array $following)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'stages', $following, IFormStage::class);

        $this->firstStage      = $firstStage;
        $this->followingStages = $following;

        $stageNumber       = 1;
        $definedFieldNames = array_fill_keys($this->firstStage->getDefinedFieldNames(), $stageNumber);

        foreach ($this->followingStages as $stage) {
            if (!$stage->areAllFieldsRequired()) {
                foreach ($stage->getRequiredFieldNames() as $requiredFieldName) {
                    if (!isset($definedFieldNames[$requiredFieldName])) {
                        throw InvalidArgumentException::format(
                                'Invalid required field in stage %d: expecting one of (%s) previously defined fields, \'%s\' given',
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
    public function getFirstStage()
    {
        return $this->firstStage;
    }

    /**
     * @param int $stageNumber
     *
     * @return IFormStage
     * @throws InvalidArgumentException
     */
    public function getStage($stageNumber)
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
    public function getStageWithFieldName($fieldName)
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
    public function getFollowingStages()
    {
        return $this->followingStages;
    }

    /**
     * @return IFormStage[]
     */
    public function getAllStages()
    {
        return array_merge([$this->firstStage], $this->followingStages);
    }

    /**
     * @return int
     */
    public function getAmountOfStages()
    {
        return 1 + count($this->followingStages);
    }

    /**
     * @inheritDoc
     */
    public function getRequiredFieldNamesForStage($stageNumber)
    {
        $stage = $this->getStage($stageNumber);

        if ($stage->areAllFieldsRequired()) {
            $requiredFieldsAsKeys = array_filter(
                    $this->fieldNameStageNumberMap,
                    function ($currentStage) use ($stageNumber) {
                        return $currentStage < $stageNumber;
                    }
            );

            return array_keys($requiredFieldsAsKeys);
        }

        $requiredFieldNames = array_fill_keys($stage->getRequiredFieldNames(), true);

        foreach ($stage->getRequiredFieldNames() as $requiredFieldName) {
            $requiredFieldNames += array_fill_keys($this->getRequiredFieldsForField($requiredFieldName), true);
        }

        return array_keys($requiredFieldNames);
    }

    protected function getRequiredFieldsForField($fieldName, IFormStage $stage = null)
    {
        $stage       = $stage ?: $this->getStageWithFieldName($fieldName);
        $fieldsNames = array_fill_keys($stage->getRequiredFieldNames(), true);

        if ($stage->areAllFieldsRequired()) {
            foreach ($this->getAllStages() as $previousStage) {
                if ($previousStage === $stage) {
                    break;
                }

                $fieldsNames += array_fill_keys($previousStage->getDefinedFieldNames(), true);
            }
        } else {
            foreach ($stage->getRequiredFieldNames() as $requiredFieldName) {
                $fieldsNames += array_fill_keys($this->getRequiredFieldsForField($requiredFieldName), true);
            }
        }

        return array_keys($fieldsNames);
    }

    /**
     * @inheritDoc
     */
    public function getFormForStage($stageNumber, array $previousStagesSubmission)
    {
        $requiredFields = $this->getRequiredFieldNamesForStage($stageNumber);

        if ($missingFields = array_diff($requiredFields, array_keys($previousStagesSubmission))) {
            throw InvalidArgumentException::format(
                    'Invalid call to %s: cannot load form of stage %d, missing required form fields (%s)',
                    __METHOD__, $stageNumber, Debug::formatValues($missingFields)
            );
        }

        $fieldsGroupedByStages = array_fill_keys(range(1, $stageNumber - 1), []);

        foreach ($requiredFields as $fieldName) {
            $stageNumberForField                           = $this->fieldNameStageNumberMap[$fieldName];
            $fieldsGroupedByStages[$stageNumberForField][] = $fieldName;
        }

        $processedSubmission = [];

        foreach ($fieldsGroupedByStages as $previousStageNumber => $fieldNames) {
            $stage        = $this->getStage($previousStageNumber);
            $formForStage = $stage->loadForm($processedSubmission);

            foreach ($formForStage->getFieldNames() as $fieldName) {
                if (isset($previousStagesSubmission[$fieldName])) {
                    $processedSubmission[$fieldName] = $formForStage
                            ->getField($fieldName)
                            ->process($previousStagesSubmission[$fieldName]);
                }
            }
        }

        return $this->getStage($stageNumber)->loadForm($processedSubmission);
    }

    /**
     * {@inheritdoc]
     */
    public function process(array $submission)
    {
        $processed = $this->firstStage->loadForm()->process($submission);

        foreach ($this->followingStages as $stage) {
            $processed += $stage->loadForm($processed)->process($submission);
        }

        return $processed;
    }

    /**
     * {@inheritdoc]
     */
    public function unprocess(array $processedSubmission)
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