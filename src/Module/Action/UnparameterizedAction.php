<?php declare(strict_types = 1);

namespace Dms\Core\Module\Action;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Form;
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
            IAuthSystem $auth,
            array $requiredPermissions,
            IUnparameterizedActionHandler $handler
    ) {
        parent::__construct($name, $auth, $requiredPermissions, $handler);
    }


    /**
     * @return IUnparameterizedActionHandler
     */
    final public function getHandler() : \Dms\Core\Module\IActionHandler
    {
        return $this->handler;
    }

    /**
     * {@inheritDoc}
     */
    public function run()
    {
        $this->verifyUserHasPermission();

        /** @var IUnparameterizedActionHandler $handler */
        $handler = $this->getHandler();

        return $handler->run();
    }
}