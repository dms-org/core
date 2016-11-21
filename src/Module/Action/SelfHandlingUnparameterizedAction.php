<?php declare(strict_types = 1);

namespace Dms\Core\Module\Action;

use Dms\Core\Auth\IAuthSystemInPackageContext;
use Dms\Core\Auth\IPermission;
use Dms\Core\Form;
use Dms\Core\Module\Handler\CustomUnparameterizedActionHandler;
use Dms\Core\Module\IUnparameterizedActionHandler;

/**
 * The self-handling unparameterized action class.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
abstract class SelfHandlingUnparameterizedAction extends UnparameterizedAction implements IUnparameterizedActionHandler
{
    /**
     * @inheritDoc
     */
    public function __construct(
        IAuthSystemInPackageContext $auth
    ) {
        parent::__construct(
            $this->name(),
            $auth,
            $this->permissions(),
            new CustomUnparameterizedActionHandler(
                function () {
                    return $this->runHandler();
                },
                $this->returnType()
            ),
            $this->metadata()
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
     * Gets the action metadata.
     *
     * @return array
     */
    protected function metadata() : array
    {
        return [];
    }

    /**
     * Gets the return dto type.
     *
     * @return string|null
     */
    abstract protected function returnType();

    /**
     * Runs the action handler.
     *
     * @return object|null
     */
    abstract protected function runHandler();
}