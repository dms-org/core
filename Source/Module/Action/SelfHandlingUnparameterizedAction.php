<?php

namespace Iddigital\Cms\Core\Module\Action;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Form;
use Iddigital\Cms\Core\Model\IDataTransferObject;
use Iddigital\Cms\Core\Module\Handler\CustomParameterizedActionHandler;
use Iddigital\Cms\Core\Module\Handler\CustomUnparameterizedActionHandler;
use Iddigital\Cms\Core\Module\IParameterizedActionHandler;
use Iddigital\Cms\Core\Module\IStagedFormDtoMapping;
use Iddigital\Cms\Core\Module\IUnparameterizedActionHandler;

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
            IAuthSystem $auth
    ) {
        parent::__construct(
                $this->name(),
                $auth,
                $this->permissions(),
                new CustomUnparameterizedActionHandler(
                        function () {
                            return $this->runHandler();
                        },
                        $this->returnDtoType()
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
     * Gets the return dto type.
     *
     * @return string|null
     */
    abstract protected function returnDtoType();

    /**
     * Runs the action handler.
     *
     * @return IDataTransferObject|null
     */
    abstract protected function runHandler();
}