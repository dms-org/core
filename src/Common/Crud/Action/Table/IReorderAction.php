<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud\Action\Table;

use Dms\Core\Auth\AdminForbiddenException;
use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Form\InvalidFormSubmissionException;

/**
 * The reorder object action interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IReorderAction extends IObjectAction
{
    const NEW_INDEX_FIELD_NAME = 'index';

    /**
     * Runs the action on the supplied object.
     *
     * @param object $object
     * @param int    $newIndex
     *
     * @return void
     * @throws AdminForbiddenException if the authenticated user does not have the required permissions
     * @throws InvalidFormSubmissionException if the form data is invalid
     */
    public function runReorder($object, int $newIndex);
}