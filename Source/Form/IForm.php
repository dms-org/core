<?php

namespace Iddigital\Cms\Core\Form;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;

/**
 * The form interface.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IForm
{
    /**
     * Gets the form processors.
     *
     * @return IFormProcessor[]
     */
    public function getProcessors();

    /**
     * Gets the form field sections.
     *
     * @return IFormSection[]
     */
    public function getSections();

    /**
     * Processes the forms submission.
     *
     * This will ignore any extra fields in the submission and will
     * only return the processed values of the form field.
     *
     * @param array $submission
     *
     * @return array The processed form submission
     * @throws InvalidFormSubmissionException If the submitted data is invalid
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

    /**
     * Gets all the fields within the form.
     *
     * @return IField[]
     */
    public function getFields();

    /**
     * Gets the names of the fields within the form.
     *
     * @return string[]
     */
    public function getFieldNames();

    /**
     * Returns whether the field with the supplied name exists.
     *
     * @param string $fieldName
     *
     * @return bool
     */
    public function hasField($fieldName);

    /**
     * Gets the field with the supplied name or null if it does not exist.
     *
     * @param string $fieldName
     *
     * @return IField
     * @throws InvalidArgumentException
     */
    public function getField($fieldName);

    /**
     * Returns the form as a single-stage form.
     *
     * @return IStagedForm
     */
    public function asStagedForm();

    /**
     * Gets the array of initial values indexed by the respective field
     * name.
     *
     * @return array[]
     */
    public function getInitialValues();
}
