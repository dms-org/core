<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud\Action\Object;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\IAuthSystemInPackageContext;
use Dms\Core\Auth\IPermission;

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
    public function __construct(IAuthSystemInPackageContext $auth)
    {
        $formDtoMapping = $this->formMapping();

        parent::__construct(
                $this->name(),
                $auth,
                $this->permissions(),
                $formDtoMapping,
                new CustomObjectActionHandler(
                        function ($object, $data = null) {
                            return $this->runHandler($object, $data);
                        },
                        $this->returnType(),
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
    abstract protected function name() : string;

    /**
     * Gets the required permissions.
     *
     * @return IPermission[]
     */
    abstract protected function permissions() : array;

    /**
     * Gets the action form mapping.
     *
     * @return IObjectActionFormMapping
     */
    abstract protected function formMapping() : IObjectActionFormMapping;

    /**
     * Gets the return dto type.
     *
     * @return string|null
     */
    abstract protected function returnType();

    /**
     * Gets the object type
     *
     * @return string
     */
    abstract protected function objectType() : string;

    /**
     * Runs the action handler.
     *
     * @param object      $object
     * @param object|null $data
     *
     * @return object|null
     */
    abstract protected function runHandler($object, $data = null);
}