<?php

namespace Iddigital\Cms\Core\Module;

use Iddigital\Cms\Core\Form;
use Iddigital\Cms\Core\Form\InvalidFormSubmissionException;
use Iddigital\Cms\Core\Form\IStagedForm;
use Iddigital\Cms\Core\Model\IDataTransferObject;
use Iddigital\Cms\Core\Persistence;

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
    public function getStagedForm();

    /**
     * Gets the type of DTO which the form submission is mapped to.
     *
     * @return string
     */
    public function getDtoType();

    /**
     * Gets the supplied form submission data mapped to a dto.
     *
     * @param array $submission
     *
     * @return IDataTransferObject
     * @throws InvalidFormSubmissionException
     */
    public function mapFormSubmissionToDto(array $submission);
}