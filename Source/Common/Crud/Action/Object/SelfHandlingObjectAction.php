<?php

namespace Iddigital\Cms\Core\Common\Crud\Action\Object;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Model\IDataTransferObject;

/**
 * The self-handling object action class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class SelfHandlingObjectAction extends ObjectAction
{
    /**
     * @inheritDoc
     */
    public function __construct(IAuthSystem $auth)
    {
        $formDtoMapping = $this->formMapping();

        parent::__construct(
                $this->name(),
                $auth,
                $this->permissions(),
                $formDtoMapping,
                new CustomObjectActionHandler(
                        function ($object, IDataTransferObject $data = null) {
                            return $this->runHandler($object, $data);
                        },
                        $this->returnDtoType(),
                        $this->objectType(),
                        $formDtoMapping->getDataDtoType()
                )
        );
    }

    /**
     * Gets the action name.
     *
     * @return string
     */
    abstract protected function name();

    /**
     * Gets the required permissions.
     *
     * @return IPermission[]
     */
    abstract protected function permissions();

    /**
     * Gets the action form mapping.
     *
     * @return IObjectActionFormMapping
     */
    abstract protected function formMapping();

    /**
     * Gets the return dto type.
     *
     * @return string|null
     */
    abstract protected function returnDtoType();

    /**
     * Gets the object type
     *
     * @return string
     */
    abstract protected function objectType();

    /**
     * Runs the action handler.
     *
     * @param object                   $object
     * @param IDataTransferObject|null $data
     *
     * @return IDataTransferObject|null
     */
    abstract protected function runHandler($object, IDataTransferObject $data = null);
}