<?php

namespace Iddigital\Cms\Core\Form;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\Stage\IndependentFormStage;

/**
 * The staged form interface.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IStagedForm
{
    /**
     * Gets the first stage.
     *
     * @return IndependentFormStage
     */
    public function getFirstStage();

    /**
     * Gets the stages after the first stage.
     *
     * @return IFormStage[]
     */
    public function getFollowingStages();

    /**
     * Gets all the stages.
     *
     * @return IFormStage[]
     */
    public function getAllStages();

    /**
     * Gets the number of stages.
     *
     * @return int
     */
    public function getAmountOfStages();

    /**
     * @param string $fieldName
     *
     * @return IFormStage
     * @throws InvalidArgumentException If no known field is defined.
     */
    public function getStageWithFieldName($fieldName);

    /**
     * @param int $stageNumber The 1-based stage number
     *
     * @return IFormStage
     * @throws InvalidArgumentException If it is out of range
     */
    public function getStage($stageNumber);

    /**
     * Gets the required field names for the supplied form stage.
     *
     * Returns an array in the format:
     * <code>
     * [
     *      <stage number> => [<array of required field names>] or '*' // '*' = all fields in stage
     * ]
     * </code>
     *
     * @param int $stageNumber
     *
     * @return string[][]|string[]
     * @throws InvalidArgumentException If the stage number is out of the range
     */
    public function getRequiredFieldGroupedByStagesForStage($stageNumber);

    /**
     * Gets the form the the stage using the previous stages submission data.
     *
     * @param int   $stageNumber
     * @param array $previousStagesSubmission
     *
     * @return IForm
     * @throws InvalidArgumentException If the stage number is out of the range
     * @throws InvalidFormSubmissionException
     */
    public function getFormForStage($stageNumber, array $previousStagesSubmission);

    /**
     * Processes the forms submission through all stages.
     *
     * @param array $submission
     *
     * @return array
     * @throws InvalidFormSubmissionException
     */
    public function process(array $submission);

    /**
     * Unprocesses the form submission back into the initial format.
     *
     * @param array $processedSubmission
     *
     * @return array
     */
    public function unprocess(array $processedSubmission);
}
