<?php declare(strict_types = 1);

namespace Dms\Core\Module;

use Dms\Core\Form;
use Dms\Core\Form\InvalidFormSubmissionException;
use Dms\Core\Form\IStagedForm;
use Dms\Core\Model\IDataTransferObject;
use Dms\Core\Persistence;

/**
 * The form to dto mapping interface.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IStagedFormDtoMapping
{
    /**
     * Gets the staged form for the input.
     *
     * @return IStagedForm
     */
    public function getStagedForm() : \Dms\Core\Form\IStagedForm;

    /**
     * Gets the type of DTO which the form submission is mapped to.
     *
     * @return string
     */
    public function getDtoType() : string;

    /**
     * Gets the supplied form submission data mapped to a dto.
     *
     * @param array $submission
     *
     * @return object
     * @throws InvalidFormSubmissionException
     */
    public function mapFormSubmissionToDto(array $submission);

    /**
     * Returns an equivalent dto mapping with the first stage of
     * the form submitted with the supplied data that is already
     * in the correct format.
     *
     * @param array $processedFirstStageData
     *
     * @return IStagedFormDtoMapping|static
     */
    public function withSubmittedFirstStage(array $processedFirstStageData);
}