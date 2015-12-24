<?php

namespace Dms\Core\Form;

use Dms\Core\Exception\InvalidArgumentException;

/**
 * The form stage interface
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IFormStage
{
    /**
     * Returns whether the stage requires the previous submission data
     * to load the form.
     *
     * @return bool
     */
    public function requiresPreviousSubmission();

    /**
     * Gets the names of the fields that are required to load the form
     * or NULL if *all* fields are required.
     *
     * @return string[]|null
     */
    public function getRequiredFieldNames();

    /**
     * Returns whether the stage requires all the previous submission data
     * to load the form.
     *
     * @return bool
     */
    public function areAllFieldsRequired();

    /**
     * Loads the form for this stage according to the previous submission.
     *
     * @param array|null $previousSubmission
     *
     * @return IForm
     * @throws InvalidArgumentException if the previous submission is not supplied and is required.
     */
    public function loadForm(array $previousSubmission = null);

    /**
     * Gets the defined field names in this form stage.
     *
     * This information is only required for fields that are
     * depended on.
     *
     * @return string[]
     */
    public function getDefinedFieldNames();
}