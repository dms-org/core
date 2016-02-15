<?php declare(strict_types = 1);

namespace Dms\Core\Form;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Stage\IndependentFormStage;

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
    public function getFirstStage() : Stage\IndependentFormStage;

    /**
     * Gets the stages after the first stage.
     *
     * @return IFormStage[]
     */
    public function getFollowingStages() : array;

    /**
     * Gets all the stages.
     *
     * @return IFormStage[]
     */
    public function getAllStages() : array;

    /**
     * Gets the number of stages.
     *
     * @return int
     */
    public function getAmountOfStages() : int;

    /**
     * Gets the form of the first stage.
     *
     * @return IForm
     */
    public function getFirstForm() : IForm;

    /**
     * @param string $fieldName
     *
     * @return IFormStage
     * @throws InvalidArgumentException If no known field is defined.
     */
    public function getStageWithFieldName(string $fieldName) : IFormStage;

    /**
     * @param int $stageNumber The 1-based stage number
     *
     * @return IFormStage
     * @throws InvalidArgumentException If it is out of range
     */
    public function getStage(int $stageNumber) : IFormStage;

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
    public function getRequiredFieldGroupedByStagesForStage(int $stageNumber);

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
    public function getFormForStage(int $stageNumber, array $previousStagesSubmission) : IForm;

    /**
     * Creates a new staged form without the first stage.
     *
     * The following stages are loaded with the supplied form data
     * for the first stage.
     *
     * @param array $firstStageSubmission
     *
     * @return static
     */
    public function submitFirstStage(array $firstStageSubmission);

    /**
     * Creates a new staged form without the first stage.
     *
     * The following stages are loaded with the supplied form data
     * for the first stage.
     *
     * @param array $processedFirstStageData
     *
     * @return static
     * @throws InvalidArgumentException
     */
    public function withSubmittedFirstStage(array $processedFirstStageData);

    /**
     * Processes the forms submission through all stages.
     *
     * @param array $submission
     *
     * @return array
     * @throws InvalidFormSubmissionException
     */
    public function process(array $submission) : array;

    /**
     * Unprocesses the form submission back into the initial format.
     *
     * @param array $processedSubmission
     *
     * @return array
     */
    public function unprocess(array $processedSubmission) : array;
}
