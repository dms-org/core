<?php declare(strict_types = 1);

namespace Dms\Core\Form;

use Dms\Core\Exception\InvalidArgumentException;

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
    public function getProcessors() : array;

    /**
     * Gets the form field sections.
     *
     * @return IFormSection[]
     */
    public function getSections() : array;

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
    public function process(array $submission) : array;

    /**
     * Returns whether the supplied array contains the expected types
     * of processed values.
     *
     * @param array $processedSubmission
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function validateProcessedValues(array $processedSubmission);

    /**
     * Unprocesses the form submission back into the initial format.
     *
     * @param array $processedSubmission
     *
     * @return array
     */
    public function unprocess(array $processedSubmission) : array;

    /**
     * Gets all the fields within the form.
     *
     * @return IField[]
     */
    public function getFields() : array;

    /**
     * Gets the names of the fields within the form.
     *
     * @return string[]
     */
    public function getFieldNames() : array;

    /**
     * Returns whether the field with the supplied name exists.
     *
     * @param string $fieldName
     *
     * @return bool
     */
    public function hasField(string $fieldName) : bool;

    /**
     * Gets the field with the supplied name or null if it does not exist.
     *
     * @param string $fieldName
     *
     * @return IField
     * @throws InvalidArgumentException
     */
    public function getField(string $fieldName) : IField;

    /**
     * Returns the form as a single-stage form.
     *
     * @return IStagedForm
     */
    public function asStagedForm() : IStagedForm;

    /**
     * Gets the array of initial (processed) values indexed by the respective field
     * name.
     *
     * @return array
     */
    public function getInitialValues() : array;

    /**
     * Returns an equivalent form with the supplied initial processed field values.
     *
     * @param array $initialProcessedValues
     *
     * @return IForm
     * @throws InvalidArgumentException
     */
    public function withInitialValues(array $initialProcessedValues) : IForm;

    /**
     * Returns an equivalent form with the field names updated
     * from the supplied array containing the old field names as the key
     * and the new field names as the value.
     *
     * @param string[] $fieldNameMap
     *
     * @return IForm
     * @throws InvalidArgumentException
     */
    public function withFieldNames(array $fieldNameMap) : IForm;
}
