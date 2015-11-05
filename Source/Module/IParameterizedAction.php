<?php

namespace Iddigital\Cms\Core\Module;

use Iddigital\Cms\Core\Auth\UserForbiddenException;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Exception\InvalidOperationException;
use Iddigital\Cms\Core\Form\InvalidFormSubmissionException;
use Iddigital\Cms\Core\Form\IStagedForm;
use Iddigital\Cms\Core\Model\IDataTransferObject;
use Iddigital\Cms\Core\Persistence;

/**
 * The parameterized action interface.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IParameterizedAction extends IAction
{
    /**
     * Gets the action handler
     *
     * @return IParameterizedActionHandler
     */
    public function getHandler();

    /**
     * Gets the form required to run this action.
     *
     * @return IStagedForm
     */
    public function getStagedForm();

    /**
     * Runs the action.
     *
     * @param array $data
     *
     * @return IDataTransferObject|null
     * @throws UserForbiddenException if the authenticated user does not have the required permissions.
     * @throws InvalidFormSubmissionException if the form data is invalid
     */
    public function run(array $data);

    /**
     * Returns an equivalent parameterized action with the first stage
     * of the form filled out with the supplied data.
     *
     * @param array $data
     *
     * @return static
     * @throws UserForbiddenException if the authenticated user does not have the required permissions.
     * @throws InvalidFormSubmissionException if the form data is invalid
     * @throws InvalidOperationException If there is only one stage
     */
    public function submitFirstStage(array $data);

    /**
     * Returns an equivalent parameterized action with the first stage
     * of the form filled out with the supplied processed data.
     *
     * This will *not* process the data through the form and is expected
     * to be in the correct format.
     *
     * @param array $processedData
     *
     * @return static
     * @throws UserForbiddenException if the authenticated user does not have the required permissions.
     * @throws InvalidArgumentException If processed form data is invalid
     * @throws InvalidOperationException If there is only one stage
     */
    public function withSubmittedFirstStage(array $processedData);
}