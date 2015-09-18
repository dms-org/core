<?php

namespace Iddigital\Cms\Core\Form;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;

/**
 * The form stage interface
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IFormStage
{
    /**
     * Returns whether the form requires the previous submission data
     * to continue.
     *
     * @return bool
     */
    public function requiresPreviousSubmission();

    /**
     * Loads the form for this stage according to the previous submission.
     *
     * @param array|null $previousSubmission
     *
     * @return IForm
     * @throws InvalidArgumentException if the previous submission is not supplied and is required.
     */
    public function loadForm(array $previousSubmission = null);
}