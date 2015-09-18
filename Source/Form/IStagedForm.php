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
     * @return IndependentFormStage
     */
    public function getFirstStage();

    /**
     * @return IFormStage[]
     */
    public function getFollowingStages();

    /**
     * @return int
     */
    public function getAmountOfStages();

    /**
     * @param int $stageNumber The 1-based stage number
     *
     * @return IFormStage
     * @throws InvalidArgumentException If it is out of range
     */
    public function getStage($stageNumber);

    /**
     * Gets the form the the stage using the previous stages submission data.
     *
     * @param int   $stageNumber
     * @param array $previousStagesSubmission
     *
     * @return IForm
     * @throws InvalidArgumentException If it is out of range
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
