<?php declare(strict_types = 1);

namespace Dms\Core\Module\Action;

use Dms\Core\Auth\IAuthSystemInPackageContext;
use Dms\Core\Module\IActionHandler;
use Dms\Core\Module\IUnparameterizedAction;
use Dms\Core\Module\IUnparameterizedActionHandler;

/**
 * The unparameterized action class.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class UnparameterizedAction extends Action implements IUnparameterizedAction
{
    /**
     * {@inheritDoc}
     */
    public function __construct(
        $name,
        IAuthSystemInPackageContext $auth,
        array $requiredPermissions,
        IUnparameterizedActionHandler $handler,
        array $metadata = []
    ) {
        parent::__construct($name, $auth, $requiredPermissions, $handler, $metadata);
    }


    /**
     * @return IUnparameterizedActionHandler
     */
    final public function getHandler() : IActionHandler
    {
        return $this->handler;
    }

    /**
     * {@inheritDoc}
     */
    public function run()
    {
        $this->verifyUserHasPermission();

        return $this->runWithoutAuthorization();
    }

    /**
     * @inheritDoc
     */
    public function runWithoutAuthorization()
    {
        /** @var IUnparameterizedActionHandler $handler */
        $handler = $this->getHandler();

        $this->auth->getEventDispatcher()->emit(
            $this->packageName . '.' . $this->moduleName . '.' . $this->getName() . '.run'
        );

        $result = $handler->run();

        $this->auth->getEventDispatcher()->emit(
            $this->packageName . '.' . $this->moduleName . '.' . $this->getName() . '.ran', $result
        );

        return $result;
    }
}