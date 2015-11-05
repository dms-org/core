<?php

namespace Iddigital\Cms\Core\Common\Crud\Action\Object;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Auth\UserForbiddenException;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Form\InvalidFormSubmissionException;
use Iddigital\Cms\Core\Model\IDataTransferObject;
use Iddigital\Cms\Core\Module\Action\ParameterizedAction;

/**
 * The object action class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectAction extends ParameterizedAction implements IObjectAction
{
    /**
     * @inheritDoc
     */
    public function __construct(
            $name,
            IAuthSystem $auth,
            array $requiredPermissions,
            IObjectActionFormMapping $formDtoMapping,
            IObjectActionHandler $handler
    ) {
        if ($formDtoMapping->getDataDtoType() !== $handler->getDataDtoType()) {
            throw TypeMismatchException::format(
                    'Cannot construct %s: data dto type %s does not match handler data dto type %s',
                    __METHOD__, $formDtoMapping->getDtoType() ?: 'null', $handler->getDtoType() ?: 'null'
            );
        }

        parent::__construct($name, $auth, $requiredPermissions, $formDtoMapping, $handler);
    }

    /**
     * Runs the action on the supplied object.
     *
     * @param object $object
     * @param array  $data
     *
     * @return IDataTransferObject|null
     * @throws UserForbiddenException if the authenticated user does not have the required permissions.
     * @throws InvalidArgumentException if the form is invalid
     * @throws InvalidFormSubmissionException if the form data is invalid
     */
    public function runOnObject($object, array $data)
    {
        /** @var IObjectActionHandler $handler */
        $handler = $this->getHandler();

        /** @var IObjectActionFormMapping $handler */
        $formMapping = $this->getFormDtoMapping();

        $handler->runOnObject($object, $formMapping->)
    }
}